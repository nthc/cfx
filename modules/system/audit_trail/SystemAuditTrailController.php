<?php
class SystemAuditTrailController extends ModelController
{
    public $modelName = '.audit_trail';
    
    public $listFields = array(
        '.audit_trail.audit_trail_id',
        '.audit_trail.audit_date',
        '.users.user_name',
        '.audit_trail.description'
    );
}