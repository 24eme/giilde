<?php

class acCouchdbJsonDefinitionParser {

    public static function parse($model, $schema, $definition) {
        return (self::parseDefinition(self::parseInheritance($model, $schema, $definition), 
                                      self::getValueRequired($schema[$model], 'definition', 'global')));
    }
    
    protected static function parseInheritance($model, $schema, $definition) {
        $inheritance_model = self::getValue($schema[$model], "inheritance", false);
        if ($inheritance_model !== false) {
            $definition->setInheritance($inheritance_model);
            return self::parse($inheritance_model, $schema, $definition);
        } else {
            return $definition;
        }
    }
    
    protected static function parseDefinition($definition, $data_definition) {
        self::parseFields($definition, self::getValueRequired($data_definition, 'fields'));
        
        return $definition;
    }

    protected static function parseFields($definition, $data_fields) {
        foreach($data_fields as $key => $data_field) {
            self::parseField($definition, $key, $data_field);
        }
        return $definition;
    }

    protected static function parseField($definition, $key, $data_field) {
        $type = self::getValue($data_field, 'type', 'string');
        $required = self::getValue($data_field, 'required', true);
        $multiple = ($key == '*');
        if ($type === acCouchdbJsonDefinitionField::TYPE_DATE) {
            $type = acCouchdbJsonDefinitionField::TYPE_STRING;
        }
        if (in_array($type, array(acCouchdbJsonDefinitionField::TYPE_STRING,
                                  acCouchdbJsonDefinitionField::TYPE_ANYONE,
                                  acCouchdbJsonDefinitionField::TYPE_INTEGER,
                                  acCouchdbJsonDefinitionField::TYPE_FLOAT))) {
            if (!$multiple) {
                $definition->add(new acCouchdbJsonDefinitionField($key, $type, $required));
            } else {
                $definition->add(new acCouchdbJsonDefinitionFieldMultiple($type));
            }
        } elseif ($type == acCouchdbJsonDefinitionField::TYPE_COLLECTION) {
            if (!$multiple) {
                self::parseDefinition(
                    $definition->add(new acCouchdbJsonDefinitionFieldCollection($key, $required, $definition->getModel(), $definition->getHash(), self::getValue($data_field, 'class', 'acCouchdbJson'), self::getValue($data_field, 'inheritance', null)))
                        ->getDefinition(),
                    self::getValueRequired($data_field, 'definition', $key)
                );
            } else {
                self::parseDefinition(
                    $definition->add(new acCouchdbJsonDefinitionFieldMultipleCollection($definition->getModel(), $definition->getHash(), self::getValue($data_field, 'class', 'acCouchdbJson'), self::getValue($data_field, 'inheritance', null)))
                        ->getDefinition(),
                    self::getValueRequired($data_field, 'definition', $key)
                );
            }
            
        } elseif ($type == acCouchdbJsonDefinitionField::TYPE_ARRAY_COLLECTION) {
            if (!$multiple) {
                self::parseDefinition(
                    $definition->add(new acCouchdbJsonDefinitionFieldArrayCollection($key, $required, $definition->getModel(), $definition->getHash(), self::getValue($data_field, 'class', 'acCouchdbJson'), self::getValue($data_field, 'inheritance', null)))
                        ->getDefinition(),
                    self::getValueRequired($data_field, 'definition', $key)
                );
            } else {
                self::parseDefinition(
                    $definition->add(new acCouchdbJsonDefinitionFieldMultipleArrayCollection($definition->getModel(), $definition->getHash(), self::getValue($data_field, 'class', 'acCouchdbJson'), self::getValue($data_field, 'inheritance', null)))
                        ->getDefinition(),
                    self::getValueRequired($data_field, 'definition', $key)
                );
            }
        } else {
            throw new acCouchdbException(sprintf("Parser Type doesn't exit : %s (%s)", $type, $definition->getHash()));
        }
        return $definition;
    }

    protected static function getValue($array, $value, $default = null) {
        if (isset($array[$value])) {
            return $array[$value];
        } else {
            return $default;
        }
    }

    protected static function getValueRequired($array, $value, $infos = null)  {
        if (is_null(self::getValue($array, $value))) {
            throw new acCouchdbException(sprintf('parse error : %s (%s)', $value, $infos));
        } else {
            return self::getValue($array, $value);
        }
    }
    
}
