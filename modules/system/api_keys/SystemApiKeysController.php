<?php
class SystemApiKeysController extends ModelController
{
    public $modelName = 'system.api_keys';
    
    public $listFields = array(
        'system.api_keys.api_key_id',
        'system.users.user_name',
        'system.api_keys.key'
    );
}
