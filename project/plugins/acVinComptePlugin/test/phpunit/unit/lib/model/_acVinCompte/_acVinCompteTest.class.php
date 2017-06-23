<?php

/* This file is part of the acVinComptePlugin package.
 * Copyright (c) 2011 Actualys
 * Authors :
 * Tangui Morlier <tangui@tangui.eu.org>
 * Vincent Laurent <vince.laurent@gmail.com>
 * Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * acVinComptePlugin test.
 *
 * @package    acVinComptePlugin
 * @subpackage lib
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @version    0.1
 */
class _acVinCompteTest extends PHPUnit_Framework_TestCase
{
  public function testSetMotDePasseSSHA()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');

	$random_pass = md5(rand());
	$object->setMotDePasseSSHA($random_pass);
	$cryptedPassword = substr($object->mot_de_passe,6);
	$this->assertEquals('{SSHA}', substr($object->mot_de_passe,0,6));
	$this->assertTrue(strlen($cryptedPassword) == 32);
  }
  public function testUpdateStatut()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');
	$updateStatut = self::getMethod('updateStatut');

	$random_pass = md5(rand());
	$object->setMotDePasseSSHA($random_pass);
  	$updateStatut->invokeArgs($object, array());
	$this->assertEquals($object::STATUT_INSCRIT, $object->statut);
	$object->mot_de_passe = str_replace('{SSHA}', '{TEXT}', $object->mot_de_passe);
  	$updateStatut->invokeArgs($object, array());
	$this->assertEquals($object::STATUT_NOUVEAU, $object->statut);
	$object->mot_de_passe = str_replace('{TEXT}', '{OUBLIE}', $object->mot_de_passe);
  	$updateStatut->invokeArgs($object, array());
	$this->assertEquals($object::STATUT_MOT_DE_PASSE_OUBLIE, $object->statut);
	$object->mot_de_passe = str_replace('{OUBLIE}', '{BIDON}', $object->mot_de_passe);
  	$updateStatut->invokeArgs($object, array());
	$this->assertNull($object->statut);

  }
  public function testSetStatut()
  {
  	$object = $this->getMockForAbstractClass('_acVinCompte');
	try {
		$object->setStatut();
    } catch (Exception $expected) {
		return;
	}
	$this->fail('An expected exception has not been raised.');
  }
  public function testGetNom()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');
	$this->assertEquals(' ', $object->getNom());
  }
  public function testGetAdresse()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');
	$this->assertEquals(' ', $object->getAdresse());
  }
  public function testGetCodePostal()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');
	$this->assertEquals(' ', $object->getCodePostal());
  }
  public function testGetCommune()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');
	$this->assertEquals(' ', $object->getCommune());
  }
  public function testGetGecos()
  {
	$object = $this->getMockForAbstractClass('_acVinCompte');
	$this->assertEquals(',,'.$object->getNom().',', $object->getGecos());
  }
  protected static function getMethod($name)
  {
	$class = new ReflectionClass('_acVinCompte');
	$method = $class->getMethod($name);
	$method->setAccessible(true);
	return $method;
  }

  protected function setUp(){ }
  protected function tearDown(){ }
}
