<?php

class AppMailer extends sfMailer
{
    public function __construct(sfEventDispatcher $dispatcher, $options)
    {
        if(in_array($options['delivery_strategy'], array(sfMailer::REALTIME, sfMailer::SPOOL)) && sfConfig::get('app_instance') == 'preprod' && sfConfig::get('app_ac_exception_notifier_email')) {
            $emails = sfConfig::get('app_ac_exception_notifier_email');
            $options['delivery_strategy'] = 'single_address';
            $options['delivery_address'] = $emails['to'];
        } elseif(in_array($options['delivery_strategy'], array(sfMailer::REALTIME, sfMailer::SPOOL)) && sfConfig::get('app_instance') == 'preprod') {
            $options['delivery_strategy'] = 'none';
        }

        parent::__construct($dispatcher, $options);
    }
}
