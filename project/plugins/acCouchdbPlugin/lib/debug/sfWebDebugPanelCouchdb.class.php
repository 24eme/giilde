<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelTimer adds a panel to the web debug toolbar with timer information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWebDebugPanelTimer.class.php 22955 2009-10-12 16:44:07Z Kris.Wallsmith $
 */
class sfWebDebugPanelCouchdb extends sfWebDebugPanel
{

  public function getTitle()
  {
    return '<img src="'.$this->webDebug->getOption('image_root_path').'/database.png" alt="CouchDB" /> '.count(CouchdbDebugManager::getQueries()).' query';
  }

  public function getPanelTitle()
  {
    return 'CouchDB';
  }

  public function getPanelContent()
  {
      $content = '<table class="sfWebDebugLogs" style="width: 500px"><tr><th>call</th><th>memory (kb)</th><th>memory (%)</th><th>time (ms)</th><th>time (%)</th></tr>';
      foreach(CouchdbDebugManager::getQueries() as $query) {
          $content .= '<tr><td class="sfWebDebugLogType" title=\''.$query['uri'].'\'><small>'.$query['method']."</small> ".$query['shortUri'].'</td><td  style="text-align: right">'.sprintf("%0.1f", round($query['memory']/1024, 1)).'</td><td style="text-align: right">'.round($query['memory'] * 100 / CouchdbDebugManager::getTotalMemory()).'</td><td style="text-align: right">'.round($query['time']*1000, 1).'</td><td style="text-align: right">'.round($query['time'] * 100 / CouchdbDebugManager::getTotalTime()).'</td></tr>';
      }

      $content .= '</table>';

      return $content;
  }

}
