
/**
 * When you choose a doctype, you must calculate the process date limit (Ajax) and execute module services
 *
 * @param doctype_id String Document type identifier
 * @param path_manage_script String Path to the php file called in the Ajax object
 * @param error_empty_type String Error label displayed when the document type is empty
 * @param action_id String Action identifier
 **/
function change_doctype(doctype_id, path_manage_script, error_empty_type, action_id, get_js_script,display_value_tr, id_res, id_coll, from_valid_qualif)
{
    var admissionDate;
    if ($('admission_date')) {
        admissionDate = $('admission_date').value;
    }
    var priorityId;
    if ($('priority')) {
        priorityId = $('priority').value;
    }
    var theCollId = path_manage_script.split('coll_id=');
    var tmp_res_id = id_res || null;
    if (theCollId[1] != '') {
        tmp_coll_id = theCollId[1];
    } else {
        var tmp_coll_id = id_coll || null;
    }
    if(doctype_id != null && doctype_id != '' && doctype_id != NaN)
    {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { type_id : doctype_id,
                          id_action : action_id,
                          res_id : tmp_res_id,
                          coll_id : tmp_coll_id,
                          admission_date : admissionDate,
                          priority_id : priorityId                          
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //alert(answer.responseText);
                if(response.status == 0  || response.status == 1)
                {
                    if(response.status == 0)
                    {
                        var limit_use = $('process_limit_date_use_yes');
                        if(limit_use)
                        {
                            limit_use.checked = true;
                        }
                        var process_date_tr = $('process_limit_date_tr');
                        if(process_date_tr != null)
                        {
                            Element.setStyle(process_date_tr, {display : display_value_tr})
                        }
                        var process_date = $('process_limit_date');
                        if(process_date != null && !from_valid_qualif)
                        {
                            process_date.value = response.process_date;
                        }
                        if (process_date.value == '') {
                            process_date.value = response.process_date;
                        }
                    }
                    else
                    {

                        var limit_use = $('process_limit_date_use_no');
                        if(limit_use)
                        {
                            limit_use.checked = true;
                        }
                        var process_date_tr = $('process_limit_date_tr');
                        if(process_date_tr != null)
                        {
                            Element.setStyle(process_date_tr, {display : 'none'})
                        }
                    }
                    var indexes = response.opt_indexes;
                    var div_indexes = $('comp_indexes');
                    if(div_indexes )
                    {
                        div_indexes.update(indexes);
                    }
                    var services_to_exec = response.services;
                    var path_scripts = '';
                    var call_func = '';

                    for(var ind=0; ind < services_to_exec.length;ind++)
                    {
                        path_scripts += services_to_exec[ind]['script'] + '$$';
                        call_func += services_to_exec[ind]['function_to_execute']+'('+services_to_exec[ind]['arguments']+');';
                    }
                    if(call_func != '' && path_scripts != '' && get_js_script != '')
                    {
                        new Ajax.Request(get_js_script,
                        {
                            method:'post',
                            parameters:
                            {
                                scripts : path_scripts
                            },
                             onSuccess: function(answer){
                                eval(answer.responseText+call_func);
                            }
                        });
                    }
                }
                else
                {
                    try{
                        $('frm_error').innerHTML = response.error_txt;
                        }
                    catch(e){}
                }
            }
        });
    }
    else
    {
        try{
            $('frm_error_'+action_id).innerHTML = error_empty_type;
            }
        catch(e){}
    }
}

/**
 * Compute process limit date from the admission date
 *
 **/
function updateProcessDate(path_manage_script)
{
    var admissionDate;
    if ($('admission_date')) {
        admissionDate = $('admission_date').value;
    }
    var typeId;
    if ($('type_id')) {
        typeId = $('type_id').value;
    }
    var priorityId;
    if ($('priority')) {
        priorityId = $('priority').value;
    }
    if (admissionDate != null && admissionDate != '' && admissionDate != NaN) {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: {
                    type_id: typeId,
                    admission_date : admissionDate,
                    priority_id : priorityId
                },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //alert(answer.responseText);
                if (response.status == 0  || response.status == 1) {
                    //console.log(response.process_date);
                    var process_date = $('process_limit_date');
                    if (response.process_date != null) {
                        process_date.value = response.process_date;
                    }
                }
            }
        });
    }
}

/**
 * Activates / Desactivates the process date limit
 *
 * @param activate Bool Activate mode = true, desactivate mode = false
 **/
function activate_process_date(activate, display_value_tr)
{
    var process_date = $('process_limit_date_tr');
    var tr_display_value = display_value_tr || 'table-row';
    if(process_date != null)
    {
        if(activate == true)
        {
            Element.setStyle(process_date, {display : tr_display_value});
            //process_date.style.display = tr_display_value;
        }
        else
        {
            //process_date.style.display = 'none';
            Element.setStyle(process_date, {display : 'none'});
        }
    }
}

/**
 * Adapts the indexing form with the category : loads the fields to display in a JSON object
 *
 * @param cat_id String Category identifier
 **/
function change_category(cat_id, display_value_tr, path_manage_script,get_js_script, params_cat)
{
    var typeContactInternal = 'not_checked';
    var typeContactExternal = 'not_checked';
    var typeMultiContactExternal = 'not_checked';
    if (cat_id == 'internal' || cat_id == 'incoming') {
        $('type_contact_external').checked = true;
    };
    if ($('type_contact_internal')) {
        if ($('type_contact_internal').checked == true) {
            var typeContactInternal = 'checked';
        }
    }
    if ($('type_contact_external')) {
        if ($('type_contact_external').checked == true) {
            typeContactExternal = 'checked';
        }
    }    
	if ($('type_multi_contact_external')) {
        if ($('type_multi_contact_external').checked == true) {
            typeMultiContactExternal = 'checked';
        }
    }
    if (typeContactInternal == 'not_checked' && typeContactExternal == 'not_checked' && typeMultiContactExternal == 'not_checked') {
        typeContactExternal = 'checked';
    }
    //Category = RM
    if(cat_id == 'rm_archive')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'doc_date_label', type:'label', state:'display'},
        {id:'originating_entity_tr', type:'tr', state:'display'},
        {id:'originating_subentity_tr', type:'tr', state:'display'},
        {id:'requesting_entity_tr', type:'tr', state:'display'},
        {id:'appraisal_code_tr', type:'tr', state:'display'},
        {id:'apparaisal_duration_tr', type:'tr', state:'display'},
        {id:'folder_id_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'name_mandatory', type:'label', state:'display'},
        {id:'originating_entity_mandatory', type:'label', state:'display'},
        {id:'originating_subentity_mandatory', type:'label', state:'hide'},
        {id:'requesting_entity_mandatory', type:'label', state:'display'},
        {id:'appraisal_code_mandatory', type:'label', state:'display'},
        {id:'apparaisal_duration_mandatory', type:'label', state:'display'},
        {id:'folder_id_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = INCOMING
    else if(cat_id == 'incoming')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'display'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'display'},
        {id:'label_dep_exp', type:'label', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'contact_choose_2_tr', type:'tr', state:'display'},
        {id:'contact_choose_3_tr', type:'tr', state:'hide'},
        {id:'dest_contact_choose_label', type:'label', state:'hide'},
        {id:'exp_contact_choose_label', type:'label', state:'display'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'hide'},
        {id:'exp_contact', type:'label', state:'display'},
        {id:'type_contact_internal', type:'radiobutton', state:typeContactInternal},
        {id:'type_contact_external', type:'radiobutton', state:typeContactExternal},
        {id:'type_multi_contact_external', type:'radiobutton', state:typeMultiContactExternal},
        {id:'folder_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'display'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = OUTGOING
    else if(cat_id == 'outgoing')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'hide'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'hide'},
        {id:'label_dep_exp', type:'label', state:'display'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'contact_choose_2_tr', type:'tr', state:'display'},
        {id:'contact_choose_3_tr', type:'tr', state:'display'},
        {id:'dest_contact_choose_label', type:'label', state:'display'},
        {id:'exp_contact_choose_label', type:'label', state:'hide'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'display'},
        {id:'exp_contact', type:'label', state:'hide'},
        {id:'type_contact_internal', type:'radiobutton', state:typeContactInternal},
        {id:'type_contact_external', type:'radiobutton', state:typeContactExternal},
        {id:'type_multi_contact_external', type:'radiobutton', state:typeMultiContactExternal},
        {id:'folder_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'hide'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'chrono_number', type:'label', state:'display'},
        {id:'chrono_number_tr', type:'tr', state:'display'},
        {id:'chrono_number_mandatory', type:'label', state:'display'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = INTERNAL
    else if(cat_id == 'internal')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'hide'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'display'},
        {id:'label_dep_exp', type:'label', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'contact_choose_2_tr', type:'tr', state:'display'},
        {id:'contact_choose_3_tr', type:'tr', state:'hide'},
        {id:'dest_contact_choose_label', type:'label', state:'hide'},
        {id:'exp_contact_choose_label', type:'label', state:'display'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'hide'},
        {id:'exp_contact', type:'label', state:'display'},
        {id:'type_contact_internal', type:'radiobutton', state:typeContactInternal},
        {id:'type_contact_external', type:'radiobutton', state:typeContactExternal},
        {id:'type_multi_contact_external', type:'radiobutton', state:typeMultiContactExternal},
        {id:'folder_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'hide'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = FOLDER_DOCUMENT
    else if(cat_id == 'folder_document')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'hide'},
        {id:'doctype_res', type:'label', state:'display'},
        {id:'priority_tr', type:'tr', state:'hide'},
        {id:'doc_date_label', type:'label', state:'display'},
        {id:'mail_date_label', type:'label', state:'hide'},
        {id:'author_tr', type:'tr', state:'display'},
        {id:'admission_date_tr', type:'tr', state:'hide'},
        {id:'nature_id_tr', type:'tr', state:'hide'},
        {id:'department_tr', type:'tr', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'hide'},
        {id:'process_limit_date_tr', type:'tr', state:'hide'},
        {id:'diff_list_tr', type:'tr', state:'hide'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'hide'},
        {id:'contact_id_tr', type:'tr', state:'hide'},
        {id:'folder_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'hide'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'display'},
        {id:'admission_date_mandatory', type:'label', state:'hide'},
        {id:'type_contact_mandatory', type:'label', state:'hide'},
        {id:'contact_mandatory', type:'label', state:'hide'},
        {id:'nature_id_mandatory', type:'label', state:'hide'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'hide'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_mandatory', type:'label', state:'hide'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }//Category = POSTINDEXING DOCUMENT
    else if(cat_id == 'postindexing_document')
    {
        var category = [
        {id:'attachment_tr', type:'tr', state:'hide'},
        {id:'attachment_mandatory', type:'label', state:'hide'},
        {id:'category_tr', type:'tr', state:'hide'},
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'display'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'display'},
        {id:'label_dep_exp', type:'label', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'dest_contact_choose_label', type:'label', state:'hide'},
        {id:'exp_contact_choose_label', type:'label', state:'display'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'hide'},
        {id:'exp_contact', type:'label', state:'display'},
        {id:'type_contact_internal', type:'radiobutton', state:typeContactInternal},
        {id:'type_contact_external', type:'radiobutton', state:typeContactExternal},
        {id:'folder_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'display'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'diff_list_tr', type:'tr', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = PURCHASE
    else if(cat_id == 'purchase')
    {
        var category = [
        {id:'category_img_purchase', type:'label', state:'display'},
        {id:'category_img_sell', type:'label', state:'hide'},
        {id:'category_img_enterprise_document', type:'label', state:'hide'},
        {id:'category_img_human_resources', type:'label', state:'hide'},
        
        {id:'doctype_tr', type:'tr', state:'display'},
        {id:'subject_tr', type:'tr', state:'display'},
		{id:'box_id_tr', type:'tr', state:'hide'},
		{id:'type_id_tr', type:'tr', state:'hide'},
		{id:'appraisal_code_tr', type:'tr', state:'hide'},
		{id:'appraisal_duration_tr', type:'tr', state:'hide'},
		{id:'access_restriction_code_tr', type:'tr', state:'hide'},
		{id:'project_tr', type:'tr', state:'hide'},
        
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'contact_label_purchase', type:'label', state:'display'},
        {id:'contact_label_sell', type:'label', state:'hide'},
        {id:'contact_label_enterprise_document', type:'label', state:'hide'},
        {id:'contact_label_human_resources', type:'label', state:'hide'},
        {id:'contact_purchase_img', type:'label', state:'display'},
        {id:'contact_sell_img', type:'label', state:'hide'},
        {id:'contact_enterprise_document_img', type:'label', state:'hide'},
        {id:'contact_human_resources_img', type:'label', state:'hide'},
        
        {id:'identifier_tr', type:'tr', state:'display'},
        {id:'doc_date_tr', type:'tr', state:'display'},
        {id:'currency_tr', type:'tr', state:'display'},
        {id:'net_sum_tr', type:'tr', state:'display'},
        {id:'tax_sum_tr', type:'tr', state:'display'},
        {id:'total_sum_tr', type:'tr', state:'display'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'folder_tr', type:'tr', state:'display'},
		{id:'difflist_tr', type:'tr', state:'display'},
        
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'hide'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'identifier_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'currency_mandatory', type:'label', state:'hide'},
        {id:'net_sum_mandatory', type:'label', state:'hide'},
        {id:'tax_sum_mandatory', type:'label', state:'hide'},
        {id:'total_sum_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_mandatory', type:'label', state:'hide'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'difflist_mandatory', type:'label', state:'display'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = SELL
    else if(cat_id == 'sell')
    {
        var category = [
        {id:'category_img_purchase', type:'label', state:'hide'},
        {id:'category_img_sell', type:'label', state:'display'},
        {id:'category_img_enterprise_document', type:'label', state:'hide'},
        {id:'category_img_human_resources', type:'label', state:'hide'},
        
        {id:'doctype_tr', type:'tr', state:'display'},
        {id:'subject_tr', type:'tr', state:'display'},
		{id:'box_id_tr', type:'tr', state:'hide'},
		{id:'type_id_tr', type:'tr', state:'hide'},
		{id:'appraisal_code_tr', type:'tr', state:'hide'},
		{id:'appraisal_duration_tr', type:'tr', state:'hide'},
		{id:'access_restriction_code_tr', type:'tr', state:'hide'},
		{id:'project_tr', type:'tr', state:'hide'},		
        
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'contact_label_purchase', type:'label', state:'hide'},
        {id:'contact_label_sell', type:'label', state:'display'},
        {id:'contact_label_enterprise_document', type:'label', state:'hide'},
        {id:'contact_label_human_resources', type:'label', state:'hide'},
        {id:'contact_purchase_img', type:'label', state:'hide'},
        {id:'contact_sell_img', type:'label', state:'display'},
        {id:'contact_enterprise_document_img', type:'label', state:'hide'},
        {id:'contact_human_resources_img', type:'label', state:'hide'},
        
        {id:'identifier_tr', type:'tr', state:'display'},
        {id:'doc_date_tr', type:'tr', state:'display'},
        {id:'currency_tr', type:'tr', state:'display'},
        {id:'net_sum_tr', type:'tr', state:'display'},
        {id:'tax_sum_tr', type:'tr', state:'display'},
        {id:'total_sum_tr', type:'tr', state:'display'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'folder_tr', type:'tr', state:'display'},
		{id:'difflist_tr', type:'tr', state:'display'},
        
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'hide'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'identifier_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'currency_mandatory', type:'label', state:'hide'},
        {id:'net_sum_mandatory', type:'label', state:'hide'},
        {id:'tax_sum_mandatory', type:'label', state:'hide'},
        {id:'total_sum_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_mandatory', type:'label', state:'hide'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'difflist_mandatory', type:'label', state:'display'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = ENTERPRISE_DOCUMENT
    else if(cat_id == 'enterprise_document')
    {
        var category = [
        {id:'category_img_purchase', type:'label', state:'hide'},
        {id:'category_img_sell', type:'label', state:'hide'},
        {id:'category_img_enterprise_document', type:'label', state:'display'},
        {id:'category_img_human_resources', type:'label', state:'hide'},
        
        {id:'doctype_tr', type:'tr', state:'display'},
        {id:'subject_tr', type:'tr', state:'display'},
		{id:'box_id_tr', type:'tr', state:'display'},
		{id:'type_id_tr', type:'tr', state:'display'},
		{id:'appraisal_code_tr', type:'tr', state:'display'},
		{id:'appraisal_duration_tr', type:'tr', state:'display'},
		{id:'access_restriction_code_tr', type:'tr', state:'display'},
		{id:'project_tr', type:'tr', state:'display'},
        
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'contact_label_purchase', type:'label', state:'hide'},
        {id:'contact_label_sell', type:'label', state:'hide'},
        {id:'contact_label_enterprise_document', type:'label', state:'display'},
        {id:'contact_label_human_resources', type:'label', state:'hide'},
        {id:'contact_purchase_img', type:'label', state:'hide'},
        {id:'contact_sell_img', type:'label', state:'hide'},
        {id:'contact_enterprise_document_img', type:'label', state:'display'},
        {id:'contact_human_resources_img', type:'label', state:'hide'},
        
        {id:'identifier_tr', type:'tr', state:'display'},
        {id:'doc_date_tr', type:'tr', state:'display'},
        {id:'currency_tr', type:'tr', state:'hide'},
        {id:'net_sum_tr', type:'tr', state:'hide'},
        {id:'tax_sum_tr', type:'tr', state:'hide'},
        {id:'total_sum_tr', type:'tr', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'folder_tr', type:'tr', state:'display'},
		{id:'difflist_tr', type:'tr', state:'hide'},
        
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'hide'},
        {id:'identifier_mandatory', type:'label', state:'hide'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'currency_mandatory', type:'label', state:'hide'},
        {id:'tax_sum_mandatory', type:'label', state:'hide'},
        {id:'total_sum_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_mandatory', type:'label', state:'hide'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'difflist_mandatory', type:'label', state:'hide'},
        {id:'folder_mandatory', type:'label', state:'hide'}		 
        ];
    }
    //Category = HUMAN_RESOURCES
    else if(cat_id == 'human_resources')
    {
        var category = [
        {id:'category_img_purchase', type:'label', state:'hide'},
        {id:'category_img_sell', type:'label', state:'hide'},
        {id:'category_img_enterprise_document', type:'label', state:'hide'},
        {id:'category_img_human_resources', type:'label', state:'display'},
        
        {id:'doctype_tr', type:'tr', state:'display'},
        {id:'subject_tr', type:'tr', state:'display'},
		{id:'box_id_tr', type:'tr', state:'hide'},
		{id:'type_id_tr', type:'tr', state:'hide'},
		{id:'appraisal_code_tr', type:'tr', state:'hide'},
		{id:'appraisal_duration_tr', type:'tr', state:'hide'},
		{id:'access_restriction_code_tr', type:'tr', state:'hide'},
		{id:'project_tr', type:'tr', state:'hide'},		
        
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'contact_label_purchase', type:'label', state:'hide'},
        {id:'contact_label_sell', type:'label', state:'hide'},
        {id:'contact_label_enterprise_document', type:'label', state:'hide'},
        {id:'contact_label_human_resources', type:'label', state:'display'},
        {id:'contact_purchase_img', type:'label', state:'hide'},
        {id:'contact_sell_img', type:'label', state:'hide'},
        {id:'contact_enterprise_document_img', type:'label', state:'hide'},
        {id:'contact_human_resources_img', type:'label', state:'display'},
        
        {id:'identifier_tr', type:'tr', state:'display'},
        {id:'doc_date_tr', type:'tr', state:'display'},
        {id:'currency_tr', type:'tr', state:'hide'},
        {id:'net_sum_tr', type:'tr', state:'hide'},
        {id:'tax_sum_tr', type:'tr', state:'hide'},
        {id:'total_sum_tr', type:'tr', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'folder_tr', type:'tr', state:'display'},
		{id:'difflist_tr', type:'tr', state:'display'},
        
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'hide'},
        {id:'identifier_mandatory', type:'label', state:'hide'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'currency_mandatory', type:'label', state:'hide'},
        {id:'tax_sum_mandatory', type:'label', state:'hide'},
        {id:'total_sum_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_mandatory', type:'label', state:'hide'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'difflist_mandatory', type:'label', state:'hide'},
        {id:'folder_mandatory', type:'label', state:'hide'}
        ];
    }
    if(params_cat)
    {
        process_category(category, display_value_tr, params_cat);
    }
    else
    {
        process_category(category, display_value_tr);
    }

    if(cat_id != null && cat_id != '' && cat_id != NaN)
    {
        //Read the actual box for this category
        //change_box(cat_id);
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { category_id : cat_id
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //~ console.log('1 '+answer.responseText);
                //~ console.log('2 '+response);
                if(response.status == 0 )
                {
                    var services_to_exec = response.services;
                    //console.log('3 '+print_r(services_to_exec));
                    var path_scripts = '';
                    var call_func = '';

                    for(var ind=0; ind < services_to_exec.length;ind++)
                    {
                        path_scripts += services_to_exec[ind]['script'] + '$$';
                        call_func += services_to_exec[ind]['function_to_execute']+'('+services_to_exec[ind]['arguments']+');';
                    }
                    //console.log(get_js_script);
                    if(call_func != '' && path_scripts != '' && get_js_script != '')
                    {
                        //console.log('OK');
                        new Ajax.Request(get_js_script,
                        {
                            method:'post',
                            parameters:
                            {
                                scripts : path_scripts
                            },
                             onSuccess: function(answer){
                                 //console.log(answer.responseText+call_func);
                                eval(answer.responseText+call_func);
                            }
                        });
                    }
                }
                else
                {
                    try{
                        $('frm_error').innerHTML = response.error_txt;
                        }
                    catch(e){}
                }
            }
        });
    }
}

/**
 * Shows all the required fields and labels for the category and hides all the others
 *
 * @param category String JSON Object of the fields to display
 **/
function process_category(category, display_value_tr, params_cat)
{
    var tr_display_val = display_value_tr || 'table-row';
    var no_param = true;
    if(params_cat)
    {
        no_param = false;
    }
    if(category != null && category.length > 0)
    {
        for(var i=0; i < category.length; i++)
        {
            var item = $(category[i]['id']);
            if(item != null)
            {
                if(category[i]['state'] == 'hide' )
                {
                    Element.setStyle(item, {display : 'none'});
                    //item.style.display = 'none';
                }
                else if(category[i]['state'] == 'display')
                {
                    if(category[i]['type'] == 'label')
                    {
                        Element.setStyle(item, {display : 'inline'});
                        //item.style.display = 'inline';
                    }
                    else if(category[i]['type'] == 'tr')
                    {
                        Element.setStyle(item, {display : tr_display_val});
                        //item.style.display = tr_display_val;
                    }
                }
                else if(category[i]['state'] == 'checked')
                {
                    item.checked = true;
                    //~ if(  no_param || typeof(params_cat[category[i]['id']]) == undefined ||  typeof(params_cat[category[i]['id']]['onchange']) == undefined || params_cat[category[i]['id']]['onchange'] == true )
                    //~ {
                        //~ item.onchange();
                    //~ }
                    if(  no_param || typeof(params_cat[category[i]['id']]) == undefined ||  typeof(params_cat[category[i]['id']]['onclick']) == undefined || params_cat[category[i]['id']]['onclick'] == true )
                    {
                        item.onclick();
                    }
                }
                else if(category[i]['state'] == 'not_checked')
                {
                    item.checked = false;
                }
            }
        }
    }
}

/** Declaration of the autocompleter object used for the contacts*/
var contact_autocompleter;

/**
 * Launch the Ajax autocomplete object to activate autocompletion on contacts
 *
 * @param path_script String Path to the Ajax script
 **/
function launch_autocompleter_contacts(path_script, id_text, id_div, cat_id)
{
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    var params = get_contacts_params();
    
    if (contact_autocompleter && contact_autocompleter.element == $$('#' + input)[0])
        contact_autocompleter.options.defaultParams = params;
    else if(path_script)
        contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
            method:'get',
            paramName:'Input',
            parameters: params,
            minChars: 2
        });
    else return false;
}

/**
 * Launch the Ajax autocomplete object to activate autocompletion on contacts en put address_id and contact_id in an hidden input
 *
 * @param path_script String Path to the Ajax script
 **/
function launch_autocompleter_contacts_v2(path_script, id_text, id_div, cat_id, contact_id, address_id)
{
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    var params = get_contacts_params();
    
    if (contact_autocompleter && contact_autocompleter.element == $$('#' + input)[0])
        contact_autocompleter.options.defaultParams = params;
    else if(path_script)
        contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
            method:'get',
            paramName:'Input',
            parameters: params,
            minChars: 2,
            afterUpdateElement: function (text, li){
                var all_li = li.id;
                var res = all_li.split(",");
                parent.$(contact_id).value = res[0];
                parent.$(address_id).value = res[1];
            }
        });
    else return false;
}

function launch_autocompleter_choose_contact(path_script, id_text, id_div, cat_id, contact_id){
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    // var params = get_contacts_params();

    contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
        method:'get',
        paramName:'what',
        // parameters: params,
        minChars: 2,
        afterUpdateElement: function (text, li){
            $(contact_id).value = li.id;
        }
    });

}

function putInSession(path_script){
    new Ajax.Request(path_script,
    {
        method:'post',
        parameters: {
            contactid : $('contactid').value
        },
        onSuccess: function(answer){
                document.location = 'index.php?display=false&dir=my_contacts&page=create_address_iframe';
        }
    });    
}

/**
 * Get the type of the contact with the category_id
 *
 * @return String category_id
 **/
function get_contact_type (category_id)
{
     if (category_id == 'purchase') {
        return 'supplier';
    } else if (category_id == 'sell') {
        return 'purchaser';
    } else if (category_id == 'enterprise_document') {
        return 'contact';
    } else if (category_id == 'human_resources') {
        return 'employee';
    } else {
        return 'letter';
    }
}

/**
 * Gets the parameters for the contacts : the table which must be use in the ajax script
 *
 * @return String parameters
 **/
function get_contacts_params(name_radio)
{
    var check = name_radio || 'type_contact';
    var arr = get_checked_values(check);
    var params = '';
    
    if (arr.length == 0) {
        var contact_type = get_contact_type($('category_id').value);
        params = 'table=contacts&contact_type=' + contact_type;
    } else {
        if (arr[0] == 'internal') {
            params = 'table=users';
        } else if(arr[0] == 'external') {
            params = 'table=contacts';
        } else {
            params = 'table=contacts';
        }
    }
    
    return params;
}

/**
 * Update the parameters of the contact_autocompleter object
 *
 **/
function update_contact_autocompleter()
{
    var params = get_contacts_params();
    if( contact_autocompleter  )
    {
        if(contact_autocompleter.options.defaultParams == null)
        {
            contact_autocompleter.options.defaultParams = 'table=contacts';
        }
        var old_param = contact_autocompleter.options.defaultParams;
        var new_param =old_param.substring(0, old_param.indexOf('table=') -1) ;
        if(new_param && new_param !='')
        {
            new_param += '&'+params;
        }
        else
        {
            new_param =  params;
        }
        contact_autocompleter.options.defaultParams = new_param;
    }
}

/**
 * Open in a popup the contact or user card
 *
 * @param path_contact_card String Path to the contact card
 * @param path_user_card String Path to the user card
 **/
function open_contact_card(path_contact_card,path_user_card,mode)
{
    if (mode == '') {
        mode = 'view';
    }
	
	if($('email')){
		var contact_value = $('email').value;
	}else{
		var contact_value = $('contact').value;
	}
    var arr = get_checked_values('type_contact');
    var contact_id = contact_value.substring(contact_value.indexOf('(')+1, contact_value.indexOf(')'));
    if (arr.length == 0) {
        //~ if (console != null) {
            //~ console.log('Erreur launch_autocompleter_contacts, no items checked');
        //~ } else {
            //~ alert('Erreur launch_autocompleter_contacts, no items checked');
        //~ }
        window.open(path_contact_card+'&id='+contact_id, 'contact_info', 'height=600, width=600,scrollbars=yes,resizable=yes');
    } else {
        if (arr[0] == 'internal') {
            window.open(path_user_card+'&id='+contact_id, 'contact_info', 'height=450, width=600,scrollbars=no,resizable=yes');
        } else if(arr[0] == 'external' || arr[0] == 'multi_external') {
            window.open(path_contact_card+'&mode='+mode+'&id='+contact_id, 'contact_info', 'height=600, width=600,scrollbars=yes,resizable=yes');
        }
    }
}

function create_contact(path_create, id_action)
{
    /*if ($('type_contact_external')) {
        $('type_contact_external').checked = true;
        $('type_contact_external').onclick();
    }*/

    
    var contact_frm = $('indexingfrmcontact');
    var contact_type = get_contact_type($('category_id').value);
    if (contact_frm) {
        var corporate = 'Y' ;
        if ($('is_corporate_N').checked == true) {
            corporate = 'N' ;
        }
        var private = 'Y' ;
        if ($('is_private_N').checked == true) {
            private = 'N' ;
        }
        var title_val = $('title').value;
        var society_val = $('society').value;
        var phone_val = $('phone').value;
        var mail_val = $('mail').value;
        var num_val =  $('num').value;
        var street_val = $('street').value;
        var add_comp_val = $('add_comp').value;
        var cp_val = $('cp').value;
        var town_val = $('town').value;
        var country_val = $('country').value;
        var comp_data_val = $('comp_data').value;
        var lastname_val = $('lastname').value;
        var firstname_val = $('firstname').value;
        var func_val = $('function').value;

        new Ajax.Request(path_create,
        {
            method:'post',
            asynchronous:false,
            parameters: {
                is_corporate : corporate,
                is_private : private,
                title : title_val,
                society : society_val,
                phone : phone_val,
                mail : mail_val,
                num : num_val,
                street : street_val,
                add_comp : add_comp_val,
                cp : cp_val,
                town : town_val,
                country : country_val,
                comp_data : comp_data_val,
                lastname : lastname_val,
                firstname : firstname_val,
                func : func_val,
                contactType : contact_type
                },
                    onSuccess: function(answer){
                    eval("response = "+answer.responseText);
                    //alert(answer.responseText);
                    if (response.status == 0 ) {
						if($('type_multi_contact_external').checked == true){
							var contact = $('email');
						}else{
							var contact = $('contact');
						}
						
                        if (contact) {
                            contact.value = response.value;
                            //$('contact_card').style.visibility = 'visible';
                            new Effect.toggle('create_contact_div', 'blind', {delay:0.2});
                            clear_form('indexingfrmcontact');
                        }
                    } else {
                        try{
                            $('frm_error_'+id_action).innerHTML = response.error_txt;
                            }
                        catch(e){}
                    }
                }
        });
    }

}

/**
 * When you change the contact type, the table used for the autocompletion change
 *
 * @param path_autocomplete String Path to the Ajax script
 **/
function change_contact_type(path_autocomplete, empty_contact_div, id_internal, id_external, id_contact, id_multi_external)
{
    var contact_id = id_contact || 'contact';
    var external_type = id_external || 'type_contact_external';
    var internal_type = id_internal || 'type_contact_internal';
    var multi_external_type = id_multi_external || 'type_multi_contact_external';
    var create_contact = $('create_contact');
    var contact_card = $('contact_card');
    var valid_multi_contact = $('valid_multi_contact');
    var email = $('email');
    var to_multi_contact = $('to_multi_contact');
    var to = $('to');
    var show_multi_contact = $('show_multi_contact_tr');
    var add_multi_contact = $('add_multi_contact_tr');
    var contact_id_tr = $('contact_id_tr');
    if(typeof(empty_contact_div) == 'undefined' || empty_contact_div == null)
    {
        var empty_contact = true ;
    }
    else
    {
        var empty_contact = empty_contact_div ;
    }
    if(create_contact)
    {
        if($(internal_type).checked == true)
        {
            Element.setStyle(create_contact, {display : 'none'});
            //create_contact.style.display = 'none';
			Element.setStyle(email, {display : 'none'});
			Element.setStyle(valid_multi_contact, {display : 'none'});
			Element.setStyle(to_multi_contact, {display : 'none'});
			Element.setStyle(to, {display : 'none'});
			Element.setStyle(show_multi_contact, {display : 'none'});
			Element.setStyle(add_multi_contact, {display : 'none'});
			Element.setStyle(contact_id_tr, {display : 'table-row'});
        }
        else if($(external_type ).checked == true || $(multi_external_type ).checked == true )
        {
            Element.setStyle(create_contact, {display : 'inline'});
            //create_contact.style.display = 'inline';
			if($(multi_external_type ).checked == true){
				Element.setStyle(email, {display : 'inline'});
				Element.setStyle(valid_multi_contact, {display : 'inline'});
				Element.setStyle(to_multi_contact, {display : 'inline'});
				Element.setStyle(to, {display : 'inline'});
				Element.setStyle(show_multi_contact, {display : 'table-row'});
				Element.setStyle(add_multi_contact, {display : 'table-row'});
				Element.setStyle(contact_id_tr, {display : 'none'});
			}else if ($(external_type ).checked == true){
				Element.setStyle(email, {display : 'none'});
				Element.setStyle(valid_multi_contact, {display : 'none'});
				Element.setStyle(to_multi_contact, {display : 'none'});
				Element.setStyle(to, {display : 'none'});
				Element.setStyle(show_multi_contact, {display : 'none'});
				Element.setStyle(add_multi_contact, {display : 'none'});
				Element.setStyle(contact_id_tr, {display : 'table-row'});
			}
        }
    }
    if(empty_contact)
    {
        $(contact_id).value='';
    }
    display_contact_card('hidden');
    update_contact_autocompleter();
}

function init_validation(path_autocomplete_contact, display_value_tr, path_manage_script, get_js_script)
{
    var param_cat = {'type_contact_internal' : {'onclick' : false} , 'type_contact_external' : {'onclick' : false}, 'type_multi_contact_external' : {'onclick' : false}};
    change_category($('category_id').value, display_value_tr,path_manage_script,get_js_script, param_cat);
    change_contact_type(path_autocomplete_contact, false);
    $('contact').onchange();
    //$('destination').onchange();
}

function clear_error(id_error)
{
    //console.log("'"+id_error+"'");
    var error_div = $(id_error);
    //console.log(error_div);
    if(error_div)
    {
        error_div.update();
    }
}

function display_contact_card(mode, id)
{
	if(id){
		var contact_card = $(id);

	}else{
		var contact_card = $('contact_card');
	}

    var contactid = $('contactid').value;

    if(contact_card && (mode == 'hidden' || mode == 'visible') && contactid != '')
    {
        Element.setStyle(contact_card, {visibility : mode});
    } else if (contactid == '') {
        Element.setStyle(contact_card, {visibility : 'hidden'});
    }
}

function changeCycle(path_manage_script)
{
    var policy_id = $('policy_id');
    if(policy_id.value != '') {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { policy_id : policy_id.value
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if(response.status == 0 || response.status == 1) {
                    if(response.status == 0) {
                        //response.selectClient;
                        $('cycle_div').innerHTML = response.selectCycle;
                    } else {
                        //
                    }
                } else {
                    try {
                        $('frm_error').innerHTML = response.error_txt;
                    }
                    catch(e){}
                }
            }
        });
    } //else {
        //if($('policy_id')) {
            //Element.setStyle($('policy_id'), {display : 'none'})
        //}
    //}
}

function getIframeContent(path_manage_script)
{
    if ($('choose_file_div')) {
        var choose_file_div = $('choose_file_div');
        if (choose_file_div.style.display == 'none') {
            var iframe = document.getElementById("file_iframe");
            var iframeContent = iframe.contentDocument;
            var templateContent2 = iframeContent.getElementById("template_content_ifr");
            //window.alert(templateContent2);
            if (templateContent2) {
                var templateContent = templateContent2.contentDocument;
                if (templateContent) {
                    var templateContentBody = templateContent.getElementById("tinymce");
                    //window.alert(templateContentBody.innerHTML);
                    new Ajax.Request(path_manage_script,
                    {
                        method:'post', asynchronous:false,
                        parameters: { template_content : templateContentBody.innerHTML
                        },
                        onSuccess: function(answer){
                            eval("response = "+answer.responseText);
                            if(response.status == 0) {
                                //
                            } else {
                                //alert(answer.responseText);
                            }
                        }
                    });
                }
            }
        }
    }
}

function convertAllBusinessAmount()
{
    if ($('net_sum_use')) {
        if ($('net_sum_use').value != '') {
            $('net_sum_preformatted').value = convertAmount($('currency').value, $('net_sum_use').value);
            $('net_sum').value = convertAmount('', $('net_sum_use').value);
        }
    }
    if ($('tax_sum_use')) {
        if ($('tax_sum_use').value != '') {
            $('tax_sum_preformatted').value = convertAmount($('currency').value, $('tax_sum_use').value);
            $('tax_sum').value = convertAmount('', $('tax_sum_use').value);
        }
    }
    if ($('total_sum_use')) {
        if ($('total_sum_use').value != '') {
            $('total_sum_preformatted').value = convertAmount($('currency').value, $('total_sum_use').value);
            $('total_sum').value = convertAmount('', $('total_sum_use').value);
        }
    }
}

function controlTotalAmount()
{
    if ($('net_sum') && $('tax_sum')) {
        if ($('net_sum').value != '' && $('tax_sum').value != '') {
            var total = parseFloat($('net_sum').value) + parseFloat($('tax_sum').value);
            //console.log(total);
            if (total != $('total_sum').value) {
                window.alert('total_sum <> net_sum + tax_sum');
            }
        }
    }
}

function computeTotalAmount()
{
    if ($('net_sum') && $('tax_sum')) {
        var total = parseFloat($('net_sum').value) + parseFloat($('tax_sum').value);
        $('total_sum').value = total;
        $('total_sum_use').value = total;
        $('total_sum_preformatted').value = convertAmount($('currency').value, total);
        //console.log(total);
    }
}

var addMultiContacts = function (idField, idList, theUrlToListScript, paramNameSrv, minCharsSrv, contact_id, address_id) {
     new Ajax.Autocompleter(
         idField,
         idList,
         theUrlToListScript,
         {
             paramName : paramNameSrv,
             minChars : minCharsSrv,
             tokens : ',',
             //afterUpdateElement : extractEmailAdress,
			parameters : 'table=contacts',
            afterUpdateElement: function (text, li){
                var all_li = li.id;
                var res = all_li.split(",");
                parent.$(contact_id).value = res[0];
                if(res[1]==' ') {
                    res[1] = 0;
                }
                parent.$(address_id).value = res[1];
            }
         });
 };
 
function updateMultiContacts(path, action, contact, target, array_index, addressid, contactid) {
        
	new Ajax.Request(path,
	{
		method:'post',
		parameters: { url : path,
					  'for': action,
					  contact: contact,
					  field: target,
					  index: array_index,
                      addressid: addressid,
                      contactid: contactid
					},
		onLoading: function(answer) {
			$('loading_' + target).style.display='inline';
		},
		onSuccess: function(answer) {
			eval("response = "+answer.responseText);
			if(response.status == 0){
				$(target).innerHTML = response.content;
				if (action == 'add') {
                    $('email').value = '';
                    $('contactid').value = '';
                    $('addressid').value = '';
                }
			} else {
				alert(response.error);
				eval(response.exec_js);
			}
			// $('loading_' + target).style.display='none';
		}
	});
}

function update_contact_type_session(path)
{
    var check = 'type_contact';
    var arr = get_checked_values(check);
    var params = '';
    
    if (arr.length == 0) {
        var contact_type = get_contact_type($('category_id').value);
        params = 'table=contacts&contact_type=' + contact_type;
    } else {
        if (arr[0] == 'internal') {
            params = 'users';
        } else if(arr[0] == 'external') {
            params = 'contacts';
        } else if(arr[0] == 'multi_external') {
            params = 'multi';
        } else {
            params = 'contacts';
        }
    }
    new Ajax.Request(path,
    {
        method:'post',
        parameters:
        {
            paramsRequest : params
        },
        onSuccess: function(answer){
            eval(answer.responseText);
        }
    });
}

function change_category_actions(path_manage_script, resId, collId)
{
    var category_id = $('category_id');
    if(category_id.value != '') {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { category_id : category_id.value, resId : resId, collId : collId
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if(response.status == 0 || response.status == 1) {
                    if(response.status == 0) {
                        $('actionSpan').innerHTML = response.selectAction;
                    } else {
                        //
                    }
                } else {
                    try {
                        $('actionSpan').innerHTML = response.error_txt;
                    }
                    catch(e){}
                }
            }
        });
    }
}

function set_new_contact_address(path_manage_script, id_div){
    new Effect.toggle(parent.document.getElementById(id_div), 'blind', {delay:0.2});  
    new Ajax.Request(path_manage_script,
    {
        method:'post',
        parameters: {},
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            parent.$('contact').value = response.contactName;
            parent.$('contactid').value = response.contactId;
            parent.$('addressid').value = response.addressId;
        }       
    });  
}

function check_date_exp(path_manage_script){
    var contact_id = $('contactid').value;
    var res_id = $('values').value;
    new Ajax.Request(path_manage_script,
    {
        method:'post',
        parameters: {contact_id : contact_id, res_id : res_id},
        onSuccess: function(answer){
            if(answer.responseText=="success"){
	    document.getElementById('contact').style.backgroundColor='#ffffff';
	    document.getElementById('contact_check').style.display='none';
	    document.getElementById('contactcheck').value = answer.responseText;
 		}else{
	    document.getElementById('contact').style.backgroundColor='#ffe09b';
	    document.getElementById('contact_check').style.display='table-row';
	    document.getElementById("contact_check").innerHTML = "<td colspan=\"3\" style=\"font-size: 9px;text-align: center;color:#ea0000;\">un courrier enregistré dans les 5 derniers jours a déjà été enregistré pour le même expéditeur</td>";
	    document.getElementById('contactcheck').value = answer.responseText;
	    }
        }       
    });  
}

function reset_check_date_exp(){
	document.getElementById('contact').style.backgroundColor='#ffffff';
	document.getElementById('contact_check').style.display='none';
	document.getElementById('contactcheck').value = 'success';
}
