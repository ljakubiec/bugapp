<?php
class Bugapp_Acl_Role_Guest implements Zend_Acl_Role_Interface
{
    public function getRoleId()
    {
        return 'guest';
    }
}
