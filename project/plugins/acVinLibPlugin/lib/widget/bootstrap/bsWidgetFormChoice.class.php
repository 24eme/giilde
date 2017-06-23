<?php

class bsWidgetFormChoice extends sfWidgetFormChoice
{
    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);

        $this->addOption('inline', true);
    }

    public function getRenderer()
    {
        if ($this->getOption('renderer'))
        {
          return $this->getOption('renderer');
        }

        if (!$class = $this->getOption('renderer_class'))
        {
          $type = !$this->getOption('expanded') ? '' : ($this->getOption('multiple') ? 'checkbox' : 'radio');
          $class = sprintf('bsWidgetFormSelect%s', ucfirst($type));
        }

        $options = $this->options['renderer_options'];
        $options['inline'] = $this->options['inline'];

        $options['choices'] = new sfCallable(array($this, 'getChoices'));

        $renderer = new $class($options, $this->getAttributes());

        // choices returned by the callback will already be translated (so we need to avoid double-translation)
        if ($renderer->hasOption('translate_choices')) {
            $renderer->setOption('translate_choices', false);
        }

        $renderer->setParent($this->getParent());

        return $renderer;
    }
    
    public function formatChoices($name, $value, $choices, $attributes)
    {
    	$onlyChoice = (!empty($attributes['only_choice'])) ? $attributes['only_choice'] : false; unset($attributes['only_choice']);
    
    	if($onlyChoice)
    	{
    		if(!isset($choices[$onlyChoice]))
    			throw new sfException("Option '$onlyChoice' doesn't exist.");
    
    		$key    = $onlyChoice;
    		$option = $choices[$key];
    		$baseAttributes = array(
    				'name'  => substr($name, 0, -2),
    				'type'  => 'radio',
    				'value' => self::escapeOnce($key),
    				'id'    => $id = $this->generateId($name, self::escapeOnce($key)),
    		);
    
    		if (strval($key) == strval($value === false ? 0 : $value))
    		{
    			$baseAttributes['checked'] = 'checked';
    		}
    
    		return $this->renderTag('input', array_merge($baseAttributes, $attributes));
    	}
    	else
    	{
    		return parent::formatChoices($name, $value, $choices, $attributes);
    	}
    }

}