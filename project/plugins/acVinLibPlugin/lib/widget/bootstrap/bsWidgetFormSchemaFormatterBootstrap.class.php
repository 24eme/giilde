<?php
class bsWidgetFormSchemaFormatterBootstrap extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "<div class='form-group'>\n%error%%label%\n %field%%help%\n%hidden_fields%</div>\n",
    $errorRowFormat  = "<li>\n%errors%</li>\n",
    $helpFormat      = '<span class="help-block">%help%</span>',
    $decoratorFormat = "<ul>\n  %content%</ul>";

    public function getErrorListFormatInARow()
    {
        return "<ul class=\"alert alert-danger list-unstyled\">\n%errors%  </ul>\n";
    }

    public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
    {
        return strtr($this->getRowFormat(), array(
          '%label%'         => $label,
          '%field%'         => $field,
          '%error%'         => $this->formatErrorsForRow($errors),
          '%help%'          => $this->formatHelp($help),
          '%hidden_fields%' => null === $hiddenFields ? '%hidden_fields%' : $hiddenFields,
        ));
    }

    public function generateLabel($name, $attributes = array())
    {
        $labelName = $this->generateLabelName($name);

        if (false === $labelName)
        {
          return '';
        }

        if (!isset($attributes['for']))
        {
          $attributes['for'] = $this->widgetSchema->generateId($this->widgetSchema->generateName($name));
        }

        if(!isset($attributes['class'])) {
            $attributes['class'] = 'control-label';            
        }

        return $this->widgetSchema->renderContentTag('label', $labelName, $attributes);
    }
}
