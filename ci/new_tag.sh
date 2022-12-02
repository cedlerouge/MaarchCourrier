#!/bin/bash

# for test 
CI_COMMIT_BRANCH='2301'

FIRST_TAG=0

TAGS=()

RESPONSE=$(curl --write-out '%{url_effective} [%{response_code}]' --silent --output /dev/null --header "PRIVATE-TOKEN: $TOKEN_GITLAB" "https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/repository/tags?search=^$CI_COMMIT_BRANCH")
RESPONSE_CODE=$(echo $RESPONSE | grep -o '\[[0-9]*\]$')

if [ $RESPONSE_CODE != '[200]' ]; then
    echo "Error ! $RESPONSE"
    exit 1
fi

for row in $(curl --header "PRIVATE-TOKEN: $TOKEN_GITLAB" "https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/repository/tags?search=^$CI_COMMIT_BRANCH" | jq -r '.[] | @base64'); do
    _jq() {
        echo ${row} | base64 --decode | jq -r ${1}
    }

    NAME=$(_jq '.name')

    IS_TMA=$(echo $NAME | grep -o '[.]*_TMA[.]*')

    if [[ -n $IS_TMA ]]; then
        echo "TMA tag branch : $NAME ! Skipping..."
    else
        TAGS+=("$NAME")
    fi
done

if [ ${#TAGS[@]} -eq 0 ]; then
    echo "No Tags available, create de first tag !"
    FIRST_TAG=1
    LATEST_TAG="$CI_COMMIT_BRANCH.0.0"
    BRANCH_TAG_VERSION=$CI_COMMIT_BRANCH
    MAJOR_TAG_VERSION='0'
    MINOR_TAG_VERSION='0'
    NEXT_TAG="$CI_COMMIT_BRANCH.0.0"
else
    SORTED_TAGS=($(echo ${TAGS[*]} | tr " " "\n" | sort -Vr))
    LATEST_TAG=$(echo ${SORTED_TAGS[0]})

    echo "Latest tag : $LATEST_TAG"

    structures=$(echo $LATEST_TAG | tr "." "\n")

    IT=1
    for item in $structures; do
        if [ $IT = 1 ]; then
            BRANCH_TAG_VERSION=$item
        fi

        if [ $IT = 2 ]; then
            MAJOR_TAG_VERSION="$item"
        fi

        if [ $IT = 3 ]; then
            MINOR_TAG_VERSION=$item
        fi

        IT=$((IT + 1))
    done
fi

echo "BRANCH : $BRANCH_TAG_VERSION"
echo "MAJOR TAG : $MAJOR_TAG_VERSION"
echo "MINOR TAG : $MINOR_TAG_VERSION"

if [[ -z $BRANCH_TAG_VERSION ]] || [[ -z $MAJOR_TAG_VERSION ]] || [[ -z $MINOR_TAG_VERSION ]]; then
    echo "Bad tag structure ! $LATEST_TAG"
    exit 1
fi

if [ $FIRST_TAG == 0 ]; then
    if [ $TAG_MODE == 'major' ]; then
        VERSION=$((MAJOR_TAG_VERSION + 1))
        NEXT_TAG="$BRANCH_TAG_VERSION.$VERSION.0"
    fi
    if [ $TAG_MODE == 'minor' ]; then
        VERSION=$((MINOR_TAG_VERSION + 1))
        NEXT_TAG="$BRANCH_TAG_VERSION.$MAJOR_TAG_VERSION.$VERSION"
    fi
    echo "NEXT TAG : $NEXT_TAG"
fi

## CREATE TAG
curl -w " => %{url_effective} [%{response_code}]" -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -X POST "https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/repository/tags?tag_name=$NEXT_TAG&ref=$CI_COMMIT_BRANCH"


if [ $FIRST_TAG == 0 ]; then
    ## CLOSE MILESTONE
    for row in $(curl --header "PRIVATE-TOKEN: $TOKEN_GITLAB" "https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/milestones?search=$LATEST_TAG" | jq -r '.[] | @base64'); do
        _jq() {
            echo ${row} | base64 --decode | jq -r ${1}
        }

        ID=$(_jq '.id')

        echo $ID

        BODY="{\"id\":\"$ID\",\"state_event\":\"close\"}"

        curl -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -d "$BODY" -X PUT https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/milestones/$ID

    done
fi

BODY="{\"id\":\"$CI_PROJECT_ID\",\"title\":\"$NEXT_TAG\"}"

# CREATE NEXT TAG MILESTONE
curl -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -d "$BODY" -X POST https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/milestones

if [ $FIRST_TAG == 0 ]; then
    # GENERATE RAW CHANGELOG
    COMMIT_LOG_FILE="tmp.txt"
    ISSUES_IDS_FILE="tmp2.txt"
    SORTED_UNIQUE_ISSUES_IDS="tmp3.txt"
    FINAL_LOG="tmp4.txt"
    CONTENT=""

    echo "Set user git : $GITLAB_USER_NAME <$GITLAB_USER_EMAIL>"

    git config --global user.email "$GITLAB_USER_EMAIL" && git config --global user.name "$GITLAB_USER_NAME" && git config core.fileMode false

    git remote set-url origin "https://gitlab-ci-token:${TOKEN_GITLAB}@${GILAB_URL}/${CI_PROJECT_PATH}"

    git fetch
    git branch -D $CI_COMMIT_BRANCH
    git pull origin $CI_COMMIT_BRANCH
    git checkout $CI_COMMIT_BRANCH

    echo "git log $LATEST_TAG..$NEXT_TAG --pretty=format:'%s' --grep='Update referential' --all-match"

    REF_UPDATED=$(git log $LATEST_TAG..$NEXT_TAG --pretty=format:'%s' --grep='Update referential' --all-match)

    echo "git log $LATEST_TAG..$NEXT_TAG --pretty=format:'%s' --grep='FEAT' --all-match"

    git log $LATEST_TAG..$NEXT_TAG --pretty=format:'%s' --grep='FEAT' --all-match >$COMMIT_LOG_FILE
    echo '' >>$COMMIT_LOG_FILE

    while IFS= read -r line; do
        ISSUE_ID=$(echo $line | grep -o 'FEAT #[0-9]*' | grep -o '[0-9]*')
        echo "$ISSUE_ID" >>$ISSUES_IDS_FILE
    done <"$COMMIT_LOG_FILE"

    echo "git log $LATEST_TAG..$NEXT_TAG --pretty=format:'%s' --grep='FIX' --all-match"

    git log $LATEST_TAG..$NEXT_TAG --pretty=format:'%s' --grep='FIX' --all-match >$COMMIT_LOG_FILE
    echo '' >>$COMMIT_LOG_FILE

    while IFS= read -r line; do
        ISSUE_ID=$(echo $line | grep -o 'FIX #[0-9]*' | grep -o '[0-9]*')
        echo "$ISSUE_ID" >>$ISSUES_IDS_FILE
    done <"$COMMIT_LOG_FILE"

    sort -u $ISSUES_IDS_FILE >$SORTED_UNIQUE_ISSUES_IDS

    while IFS= read -r line; do
        echo "=================="
        echo $line
        curl -H "X-Redmine-API-Key: ${REDMINE_API_KEY}" -H 'Content-Type: application/json' -X GET https://forge.maarch.org/issues/$line.json >issue_$line.json
        # echo `cat issue_$line.json`
        SUBJECT=$(cat issue_$line.json | jq -r '.issue.subject')
        TRACKER=$(cat issue_$line.json | jq -r '.issue.tracker.name')
        ID=$(cat issue_$line.json | jq -r '.issue.id')
        echo ""
        echo "ID : $ID"
        echo "TRACKER : $TRACKER"
        echo "SUBJECT : $SUBJECT"
        echo "=================="

        if [ ! -z $ID ]; then
            echo "* **$TRACKER [#$ID](https://forge.maarch.org/issues/$ID)** - $SUBJECT" >>$FINAL_LOG
        fi
    done <"$SORTED_UNIQUE_ISSUES_IDS"

    if [[ ! -z $REF_UPDATED ]]; then
        echo "* **Fonctionnalité** - Mise à jour de la BAN 75" >>$FINAL_LOG
    fi

    sort -u $FINAL_LOG >>changelog.txt

    while IFS= read -r line; do
        CONTENT="$CONTENT\n$line"
    done <"changelog.txt"

    echo $CONTENT

    # Update tag release
    BODY="{\"description\":\"$CONTENT\",\"tag_name\":\"$NEXT_TAG\", \"milestones\": [\"$NEXT_TAG\"]}"

    curl -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -d "$BODY" -X POST https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/releases

    # Update files version
    cp package.json tmp_package.json

    jq -r ".version |= \"$NEXT_TAG\"" tmp_package.json >package.json

    rm tmp_package.json

    git add -f package.json

    mkdir -p ci/build/
    mv $COMMIT_LOG_FILE ci/build/
    mv $ISSUES_IDS_FILE ci/build/
    mv $SORTED_UNIQUE_ISSUES_IDS ci/build/
    mv $FINAL_LOG ci/build/

    git commit -m "Update next tag version files : $NEXT_TAG"

    git push origin $CI_COMMIT_BRANCH

fi
