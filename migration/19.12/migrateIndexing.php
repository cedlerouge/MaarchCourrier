<?php

require '../../vendor/autoload.php';

chdir('../..');

$migrated = 0;
$customs =  scandir('custom');
foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $groupBasket = \Basket\models\GroupBasketModel::get(['select' => ['group_id'], 'where' => ['basket_id = ?'], 'data' => ['IndexingBasket']]);

    foreach ($groupBasket as $value) {
        $hasService = \SrcCore\models\DatabaseModel::select([
            'select'    => [1],
            'table'     => ['usergroups_services'],
            'where'     => ['group_id = ?', 'service_id = ?'],
            'data'      => [$value['group_id'], 'index_mlb']
        ]);

        if (!empty($hasService)) {
            $actions = [];
            $entities = [];
            $keywords = [];
            \Group\models\GroupModel::update([
                'set'   => ['can_index' => 'true'],
                'where' => ['group_id = ?'],
                'data'  => [$value['group_id']]
            ]);

            // ACTIONS WITHOUT DIRECT STATUS
            $actionsWithStatusesToCreate = \SrcCore\models\DatabaseModel::select([
                'select'    => ['status_id', 'action_id'],
                'table'     => ['groupbasket_status'],
                'where'     => ['group_id = ?', 'basket_id = ?'],
                'data'      => [$value['group_id'], 'IndexingBasket'],
                'order_by'  => ['"order"']
            ]);

            foreach ($actionsWithStatusesToCreate as $item) {
                $existingActions = \SrcCore\models\DatabaseModel::select([
                    'select'    => ['id'],
                    'table'     => ['actions'],
                    'where'     => ['id_status = ?', '(action_page = ? OR component = ?)'],
                    'data'      => [$item['status_id'], 'confirm_status', 'confirmAction']
                ]);

                if (!empty($existingActions[0])) {
                    $actions[] = (string)$existingActions[0]['id'];
                } else {
                    $statusLabel = \Status\models\StatusModel::getById(['id' => $item['status_id'], 'select' => ['label_status']]);

                    $id = \Action\models\ActionModel::create([
                        'label_action'  => "Enregistrer vers le status : {$statusLabel['label_status']}" ,
                        'id_status'     => $item['status_id'],
                        'history'       => 'Y',
                        'component'     => 'confirmAction'
                    ]);
                    \Action\models\ActionModel::createCategories(['id' => $id, 'categories' => ['incoming', 'outgoing', 'internal', 'ged_doc']]);

                    $actions[] = (string)$id;
                }

            }

            // ACTIONS WITH STATUS
            $actionsToMigrate = \SrcCore\models\DatabaseModel::select([
                'select'    => ['id_action'],
                'table'     => ['actions_groupbaskets, actions'],
                'where'     => ['group_id = ?', 'basket_id = ?', 'id_action = id', '(action_page = ? OR component = ? OR action_page = ?)'],
                'data'      => [$value['group_id'], 'IndexingBasket', 'confirm_status', 'confirmAction', "''"]
            ]);

            foreach ($actionsToMigrate as $item) {
                if (!in_array($item['id_action'], $actions)) {
                    $actions[] = (string)$item['id_action'];
                }
            }

            // KEYWORDS + ENTITIES
            $keywordsAndEntities = \SrcCore\models\DatabaseModel::select([
                'select'    => ['entity_id', 'keyword'],
                'table'     => ['groupbasket_redirect'],
                'where'     => ['group_id = ?', 'basket_id = ?', 'redirect_mode = ?'],
                'data'      => [$value['group_id'], 'IndexingBasket', 'ENTITY']
            ]);
            foreach ($keywordsAndEntities as $item) {
                if (!empty($item['keyword']) && !in_array($item['keyword'], $keywords)) {
                    $keywords[] = $item['keyword'];
                } elseif (!empty($item['entity_id'])) {
                    $entityToMigrate = \Entity\models\EntityModel::getByEntityId(['entityId' => $item['entity_id'], 'select' => ['id']]);
                    if (!in_array($entityToMigrate['id'], $entities)) {
                        $entities[] = (string)$entityToMigrate['id'];
                    }
                }
            }

            // UPDATE INDEXING PARAMS
            \Group\models\GroupModel::update([
                'set'   => ['indexation_parameters' => json_encode(['actions' => $actions, 'entities' => $entities, 'keywords' => $keywords])],
                'where' => ['group_id = ?'],
                'data'  => [$value['group_id']]
            ]);

            $migrated++;
        }
    }
    printf("Migration Indexing Basket (CUSTOM {$custom}) : " . $migrated . " groupe(s) avec le service et la bannette IndexingBasket trouvé(s) et migré(s).\n");
}

