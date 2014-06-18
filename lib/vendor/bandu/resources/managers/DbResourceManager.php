<?php

namespace Bandu\Orm;

use Bandu\Database\MySQLWrapper;

abstract class DbResourceManager {

    protected static $OPERATORS = array(
        '_gt' => '> %s',
        '_gte' => '>= %s',
        '_lt' => '< %s',
        '_lte' => '<= %s',
        '_ne' => '!= %s',
        '_in' => 'IN (%s)',
        '_nin' => 'NOT IN (%s)',
    );
    protected $resourceClass;

    /**
     * @var \Bandu\Database\MySQLWrapper
     */
    protected $db;
    protected $queries;
    protected $defaults;
    protected $properties;
    protected $associations;
    protected $collections;
    protected $metaData;

    public function __construct(MySQLWrapper $db) {
        $this->db = $db;
        $this->init();
        $this->loadConfig();
        $this->generateResourceMetaData();
        $this->generateQueries();
    }

    protected function init() {
        $this->queries = array();
        $this->defaults = array();
        $this->properties = array();
        $this->associations = array();
        $this->collections = array();
        $this->metaData = array(
            'properties' => array(),
            'associations' => array(),
            'collections' => array(),
        );
    }

    public function loadConfig() {
        $this->loadDefaults()
                ->loadProperties()
                ->loadAssociations()
                ->loadCollections();
        return $this;
    }

    public function create(&$resource) {
        $this->createResourceProperties($resource);
        $this->createResourceAssociations($resource);
        return true;
    }

    public function retrieve(&$resource) {
        $this->retrieveResourceProperties($resource);
        $this->retrieveResourceAssociations($resource);
        return true;
    }

    public function update(&$resource) {
        $this->updateResourceProperties($resource);
        $this->updateResourceAssociations($resource);
        return true;
    }

    public function delete(&$resource) {
        $properties = $this->getResourceProperties($resource);
        $deleteAssociationsQueries = $this->populateQueries('delete', 'associations', $properties);
        foreach ($deleteAssociationsQueries as $q) {
            $this->db->execute($q);
        }
        $deletePropertiesQueries = $this->populateQueries('delete', 'properties', $properties);
        foreach ($deletePropertiesQueries as $q) {
            $this->db->execute($q);
        }
        unset($user);
        return true;
    }

    public function find(array $criteria) {
        return $this->searchAssociations($criteria);
    }

    protected function retrieveResourceProperties(&$resource) {
        $properties = array();
        foreach ($this->getProperties() as $prop => $data) {
            $getter = 'get' . ucfirst($prop);
            $value = $resource->$getter();
            if (!is_null($value)) {
                $properties[] = sprintf("%s = '%s'", $data['field'], $value);
            }
        }
        $whereClause = implode(' AND ', $properties);
        $query = $this->queries['retrieve']['properties'][0];
        $retrieveQuery = str_replace(':where', $whereClause, $query);
        if ($this->db->execute($retrieveQuery)) {
            $resourceProperties = $this->db->fetchRow();
            $resource->setProperties($resourceProperties);
        }
    }

    protected function retrieveResourceAssociations(&$resource) {
        if (count($this->associations)) {
            $properties = $this->getResourceProperties($resource);
            $retrieveAssociationsQueries = $this->populateQueries('retrieve', 'associations', $properties);
            foreach ($retrieveAssociationsQueries as $property => $q) {
                if ($this->db->execute($q)) {
                    $associations = $this->db->fetchAll();
                    $setter = "set" . ucfirst($property);
                    $resource->$setter($associations);
                }
            }
        }
    }

    protected function populateQueries($action, $type, $arguments) {
        $populatedQueries = array();
        foreach ($this->queries[$action][$type] as $key => $q) {
            foreach ($arguments as $p => $property) {
                $q = str_replace(array($p), "'$property'", $q);
            }
            $populatedQueries[$key] = $q;
        }
        return $populatedQueries;
    }

    protected function createResourceProperties(&$resource) {
        $properties = $this->getResourceProperties($resource);
        $createQueries = $this->populateQueries('create', 'properties', $properties);
        try {
            $id = $this->db->execute($createQueries[0]);
            $resource->setId($id);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        return $resource;
    }

    protected function createResourceAssociations(&$resource) {
        if (!count($this->associations)) {
            return $resource;
        }
        $associations = $this->getResourceAssociations($resource);
        if (!count($associations)) {
            return $resource;
        }
        $this->populateQueries('create', 'associations', $associations);
        foreach ($this->queries['create']['associations'] as $q) {
            $arguments = array();
            foreach ($associations as $property => $values) {
                foreach ($values as $assoc) {
                    $args = array();
                    foreach ($assoc as $value) {
                        $args[] = "'$value'";
                    }
                    $arguments[] = "(" . implode(", ", $args) . ")";
                }
            }
            $createQueries[] = str_replace(":values", implode(", ", $arguments), $q);
        }
        foreach ($createQueries as $q) {
            $this->db->execute($q);
        }
        return $resource;
    }

    protected function updateResourceProperties($resource) {
        $properties = $this->getResourceProperties($resource);
        $updateQueries = $this->populateQueries('update', 'properties', $properties);
        foreach ($updateQueries as $q) {
            $this->db->execute($q);
        }
        return $resource;
    }

    protected function updateResourceAssociations($resource) {
        $properties = $this->getResourceProperties($resource);
        $deleteQueries = $this->populateQueries("delete", "associations", $properties);
        foreach ($deleteQueries as $q) {
            $this->db->execute($q);
        }
        $this->createResourceAssociations($resource);
        return $resource;
    }

    abstract protected function getDefaults();

    abstract protected function getProperties();

    abstract protected function getAssociations();

    abstract protected function getCollections();

    protected function loadDefaults() {
        $this->defaults = $this->getDefaults();
        return $this;
    }

    protected function loadProperties() {
        foreach ($this->getProperties() as $property => $p) {
            $this->properties[$property] = new Property($property, $p);
        }
        return $this;
    }

    protected function loadAssociations() {
        foreach ($this->getAssociations() as $property => $a) {
            $this->associations[$property] = new Association($property, $a);
        }
        return $this;
    }

    protected function loadCollections() {
        foreach ($this->getCollections() as $property => $c) {
            $this->collections[$property] = new Collection($property, $c);
        }
        return $this;
    }

    protected function generateResourceMetaData() {
        $this->loadPropertiesMetaData();
        $this->loadAssociationsMetaData();
    }

    protected function loadPropertiesMetaData() {
        $propertiesData = array();
        foreach ($this->properties as $p) {
            $table = $this->getPropertyTable($p);
            if (!array_key_exists($table, $propertiesData)) {
                $propertiesData[$table] = array();
            }
            $propertiesData[$table][$p->name] = $p->field;
        }
        $this->metaData['properties'] = $propertiesData;
        return $this;
    }

    protected function getPropertyTable(Property $p) {
        if (array_key_exists('table', $this->defaults)) {
            if (!is_null($this->defaults['table'])) {
                return $this->defaults['table'];
            }
        }
        throw new \Exception("No Table Defined For Property: " . $p->name);
    }

    protected function loadAssociationsMetaData() {
        $associationsData = array();
        foreach ($this->associations as $a) {
            $associationsData[$a->name] = array(
                'table' => $a->table,
                'fields' => $a->fields,
                'filter' => $a->filter,
                'callback' => $a->callback,
            );
        }
        $this->metaData['associations'] = $associationsData;
        return $this;
    }

    protected function generateQueries() {
        $this->generateCreateQueries()
                ->generateRetrieveQueries()
                ->generateUpdateQueries()
                ->generateDeleteQueries();
    }

    protected function generateCreateQueries() {
        $callback = function (&$item) {
                    $item = ":$item";
                };

        foreach ($this->metaData['properties'] as $table => $input) {
            $fields = implode(', ', array_values($input));
            $values = array_keys($input);

            array_walk($values, $callback);

            $args = array(
                $table,
                $fields,
                implode(', ', $values),
            );
            $this->queries['create']['properties'][] = vsprintf("INSERT INTO %s (%s) VALUES (%s)", $args);
        }
        foreach ($this->metaData['associations'] as $property => $input) {
            $assocArgs = array(
                $input['table'],
                implode(', ', array_merge(array_values($input['filter']), $input['fields'])),
                $this->getAssociationFilter($property),
            );
            $this->queries['create']['associations'][] = vsprintf("INSERT INTO %s(%s) VALUES :values", $assocArgs);
        }
        return $this;
    }

    protected function generateRetrieveQueries() {
        foreach ($this->metaData['properties'] as $table => $input) {
            $fields = array();
            foreach ($input as $property => $field) {
                $fields[] = "$field AS $property";
            }
            $args = array(
                implode(', ', $fields),
                $table,
            );
            $this->queries['retrieve']['properties'][] = vsprintf("SELECT %s FROM %s WHERE :where", $args);
        }
        foreach ($this->metaData['associations'] as $property => $input) {
            $assocArgs = array(
                implode(', ', $input['fields']),
                $input['table'],
                $this->getAssociationFilter($property),
            );
            $this->queries['retrieve']['associations'][$property] = vsprintf("SELECT %s FROM %s WHERE %s", $assocArgs);
        }
        return $this;
    }

    protected function generateUpdateQueries() {
        $fieldValues = array();
        foreach ($this->metaData['properties'] as $table => $input) {
            foreach ($input as $property => $field) {
                $fieldValues[] = "$field = :$property";
            }
            $args = array(
                $table,
                implode(', ', $fieldValues),
                $this->getResourceFilter(),
            );
            $this->queries['update']['properties'][] = vsprintf("UPDATE %s SET %s WHERE %s", $args);
        }
        return $this;
    }

    protected function generateDeleteQueries() {
        foreach ($this->metaData['associations'] as $property => $input) {
            $assocArgs = array(
                $input['table'],
                $this->getAssociationFilter($property),
            );
            $this->queries['delete']['associations'][] = vsprintf("DELETE FROM %s WHERE %s", $assocArgs);
        }
        foreach ($this->metaData['properties'] as $table => $input) {
            $args = array(
                $table,
                $this->getResourceFilter(),
            );
            $this->queries['delete']['properties'][] = vsprintf("DELETE FROM %s WHERE %s", $args);
        }
        return $this;
    }

    protected function getResourceFilter() {
        $filter = array();
        foreach ($this->defaults['filter'] as $prop => $field) {
            $filter[] = "$field = :$prop";
        }
        return implode(" AND ", $filter);
    }

    protected function getAssociationFilter($property) {
        $assoc = $this->metaData['associations'][$property];
        $filter = array();
        foreach ($assoc['filter'] as $prop => $field) {
            $filter[] = "$field = :$prop";
        }
        return implode(" AND ", $filter);
    }

    protected function getResourceProperties($resource) {
        $resourceProperties = array();
        foreach (array_keys($this->getProperties()) as $property) {
            $getProperty = "get" . ucfirst($property);
            $resourceProperties[":$property"] = $resource->$getProperty();
        }
        return $resourceProperties;
    }

    protected function getResourceAssociations($resource) {
        $resourceAssociations = array();
        foreach ($this->getAssociations() as $property => $properties) {
            $assoc = array();
            foreach ($properties['filter'] as $prop => $field) {
                $getProperty = "get" . ucfirst($prop);
                $assoc[$field] = $resource->$getProperty();
            }
            $getter = "get" . ucfirst($property);
            if (is_array($resource->getter())) {
                foreach ($resource->$getter() as $association) {
                    $resourceAssociations[$property][] = array_merge($assoc, $association);
                }
            }
        }
        return $resourceAssociations;
    }

    protected function searchProperties(array $criteria) {
        $properties = array_keys($this->getProperties());
        $params = $this->getParametersFor($properties, $criteria);

        $fields = implode(', ', $this->defaults['filter']);

        $this->db->select($this->defaults['table'], $fields, $params);
        return $this->fetchResources();
    }

    protected function searchAssociations(array $criteria) {
        $associations = $this->getAssociations();
        $params = $this->getParametersFor(array_keys($associations), $criteria);

        $results = array();
        foreach ($params as $param => $indexes) {
            $table = $associations[$param]['table'];
            $fields = implode(', ', $associations[$param]['filter']);
            $where = $this->buildAssociatedWhereClause($param, $indexes);
            $this->db->select($table, $fields, $where);
            $results = $this->fetchAssociatedResources($associations[$param]['filter']);
        }
        return $results;
    }

    protected function buildAssociatedWhereClause($param, $indexes) {
        $associationSearchOptions = $this->associations[$param]->searchOptions;
        $where = array();
        foreach ($indexes as $index => $operators) {
            $keyField = $associationSearchOptions[$index]['key'];
            $valueField = $associationSearchOptions[$index]['value'];
            foreach ($operators as $comparison => $operator) {
                $where[$keyField] = $index;
                $where[$valueField] = array($comparison => $operator);
            }
        }
        return $where;
    }

    protected function fetchAssociatedResources($referenceMapping) {
        if (!$this->db->getNumRows()) {
            return array();
        }
        $results = array();
        foreach ($this->db->fetchAll() as $associated) {
            $resourceCriteria = array();
            foreach ($referenceMapping as $field => $ref) {
                $resourceCriteria[$field] = $associated[$ref];
            }
            $resource = new $this->defaults['resource']($resourceCriteria);
            $this->retrieve($resource);
            $results[] = $resource;
        }
        return $results;
    }

    protected function getParametersFor(array $keys, array $criteria) {
        $params = array();
        foreach ($keys as $key) {
            if (!array_key_exists($key, $criteria)) {
                continue;
            }
            $params[$key] = $criteria[$key];
        }
        return $params;
    }

    protected function fetchResources() {
        $results = array();
        if ($this->db->getNumRows()) {
            foreach ($this->db->fetchAll() as $match) {
                $resourceClass = $this->getResourceClass();
                $r = new $this->defaults['resource']($match);
                $this->retrieve($r);
                $results[] = $r;
            }
        }
        return $results;
    }

}
