<?php
class SystemAuditTrailController extends ModelController
{
    public $modelName = 'system.audit_trail';
    
    public $listFields = array(
        'system.audit_trail.audit_trail_id',
        'system.audit_trail.audit_date',
        'system.users.user_name',
        'system.audit_trail.description'
    );
}