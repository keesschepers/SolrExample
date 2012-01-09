<?php

class Application_Acl extends Zend_Acl {

    /**
     * Doctrine EntityManager
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * Config
     *
     * @var Zend_Config
     */
    protected $_config;

    /**
     * Mapping namespace
     *
     * @var string
     */
    protected $_mappingNamespace;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_em = Zend_Registry::get('doctrine')->getEntityManager();
        $this->_config = Zend_Registry::get('config');
        $this->_mappingNamespace = $this->_config->resources->doctrine->orm->entityManagers->default
                ->metadataDrivers->drivers->{0}->mappingNamespace;
        $this->_addRoles();
        $this->_addResources();
        $this->_setAuthorization();
    }

    /**
     * Adds roles to ACL
     */
    protected function _addRoles() {
        // Add default guest role for anonymous users
//        $this->addRole(new Zend_Acl_Role('guest'));

        $query = $this->_em->createQuery(sprintf('SELECT ug.name FROM %s\UserGroup ug ', $this->_mappingNamespace));
        $usergroups = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        
        // Add configured roles        
        foreach ($usergroups as $group) {
            $this->addRole(new Zend_Acl_Role($group['name']));
        }
    }

    /**
     * Adds resources to ACL
     */
    public function _addResources() {
        foreach (self::getResourcesAsArray() as $resource => $humanValue) {
            $this->addResource(new Zend_Acl_Resource($resource));
        }
    }

    /**
     * Sets authorization
     */
    public function _setAuthorization() {
        $query = $this->_em->createQueryBuilder()
                ->select('p.module', 'p.controller', 'p.action', 'ug.name AS userGroup')
                ->from($this->_mappingNamespace . '\Permission', 'p')
                ->join('p.userGroup', 'ug')
                ->getQuery();
        $permissions = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        
        foreach ($permissions as $permission) {
            $resourceName = strtolower(implode('_', array(
                $permission['module'], 
                $permission['controller'], 
                $permission['action']
            )));
            
            $roleName = strtolower($permission['userGroup']);
            if ($this->hasRole($roleName) && $this->has($resourceName)) {
                $this->allow($roleName, $resourceName);
            }
        }
    }

    /**
     *
     * Retrieve all resources based on reflection
     *
     * @param  $flat Indicates wether the users wants te array returned to be flattend
     * @return string
     */
    public static function getResourcesAsArray($flat = true) {
        $resourceReflection = new Pike_Reflection_Resource();

        if ($flat) {
            $resources = $resourceReflection->toFlatArray();
        } else {
            $resources = $resourceReflection->toArray();
        }

        // Merge configured resources
        $config = Zend_Registry::get('config');

        if (isset($config->permissions) && isset($config->buza->permissions)) {
            $permissions = array_merge_recursive($config->buza->permissions->toArray(), $config->permissions->toArray());
        } elseif (isset($config->permissions)) {
            $permissions = $config->permissions->toArray();
        } elseif (isset($config->buza->permissions)) {
            $permissions = $config->buza->permissions->toArray();
        }

        if (isset($permissions)) {
            foreach ($permissions as $module => $controllers) {
                foreach ($controllers as $controller => $actions) {
                    foreach ($actions as $action => $options) {
                        if ($flat) {
                            $resources[strtolower(implode('_', array($module, $controller, $action)))] = '';
                        } else {
                            $resources[$module][$controller][$action] = $options;
                        }
                    }
                }
            }
        }

        return $resources;
    }

}