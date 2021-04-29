<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

fn_register_hooks(
    'render_block_pre',
    'render_block_post',
    'smarty_block_hook_post',
    'init_templater_post',
    'dispatch_before_send_response',
    'registry_save_pre',
    'register_cache',
    'sucess_user_login',
    'user_logout_after',
    'data_keeper_backup_files',
    'url_post',
    'update_customization_mode',

    // Used to trigger VCL regeneration requirement
    'update_company',
    'create_seo_name_post',
    'settings_update_value_by_id_post',
    'update_addon_status_post'
);
