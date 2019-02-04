<?php

/* This file is part of the acLdapPlugin package.
 * Copyright (c) 2011 Actualys
 * Authors :
 * Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * Vincent Laurent <vince.laurent@gmail.com>
 * Tangui Morlier <tangui@tangui.eu.org>
 * Charlotte De Vichet <c.devichet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * acLdapPlugin lib.
 *
 * @package    acLdapPlugin
 * @subpackage lib
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @author     Tangui Morlier <tangui@tangui.eu.org>
 * @author     Charlotte De Vichet <c.devichet@gmail.com>
 * @version    0.1
 */
abstract class acVinLdap
{
    protected $serveur;
    protected $dn;
    protected $dc;
    protected $pass;

    private $connection = null;
    private $base_dn = '';

    private $base_identifiant = '';

    /**
     * Constructeur.
     * Défini quelques variables et teste la connection
     *
     */
    public function __construct()
    {
        $this->serveur = sfConfig::get('app_ldap_serveur');
        $this->dn = sfConfig::get('app_ldap_dn');
        $this->dc = sfConfig::get('app_ldap_dc');
        $this->pass = sfConfig::get('app_ldap_pass');

        $this->base_dn = $this->ou . ',' . $this->dc;
        $this->base_identifiant = $this->id
                                . '=%s,'
                                . $this->base_dn;
        $this->connection = $this->connect();
    }

    /**
     * Tente de se connecter au LDAP
     *
     * @return bool Connection réussie
     */
    public function connect()
    {
        $con = ldap_connect($this->serveur);
        ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);

        return ($con && ldap_bind($con, $this->dn, $this->pass)) ? $con : false;
    }

    /**
     * Retourne l'identifiant de connection
     *
     * @return resource Identifiant LDAP
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Retourne le DN de base pour la recherche
     *
     * @return string Base DN pour la recherche
     */
    protected function getBaseDN()
    {
        return $this->base_dn;
    }

    /**
     * Retourne le template d'identification
     *
     * @return string Template de la ressource
     */
    protected function getBaseIdentifiant()
    {
        return $this->base_identifiant;
    }

    /**
     * Sauvegarde une entrée dans le LDAP
     *
     * @param string $identifiant Identifiant de la ressource. uid ou cn
     * @param array $attributes Attributs de l'entrée.
     * @return bool Retourne false si échec de la sauvegarde
     */
    public function save($identifiant, $attributes)
    {
        if($this->connection) {
            return ($this->exist($identifiant))
                ? $this->update($identifiant, $attributes)
                : $this->add($identifiant, $attributes);
        }
        return false;
    }

    /**
     * Créé une nouvelle entrée.
     *
     * @param string $identifiant Identifiant de la ressource.
     * @param array $attributes Attributs de la ressource.
     * @return bool Retourne True si ajout, false si échec.
     * @throws sfException Si échec de l'ajout
     */
    protected function add($identifiant, $attributes)
    {
        if($this->connection) {
            if (! @ldap_add($this->connection,
                             sprintf($this->base_identifiant, $identifiant),
                             $attributes)
            ) {
                throw new sfException(ldap_error($this->connection));
            }
            return true;
        }
        return false;
    }

    /**
     * Met à jour une entrée dans le LDAP
     *
     * @param string $identifiant Identifiant de la ressource
     * @param array $attributes Attributs de la ressource
     * @return bool Réussite de la modification
     * @throws sfException si la modification échoue
     */
    protected function update($identifiant, $attributes)
    {
        if($this->connection) {
            if (! @ldap_modify($this->connection,
                               sprintf($this->base_identifiant, $identifiant),
                               $attributes)
            ) {
                throw new sfException(ldap_error($this->connection));
            }
            return true;
        }
        return false;
    }

    /**
     * Supprime une entrée dans le LDAP
     *
     * @param string $identifiant Identifiant de la ressource
     * @return bool Suppression réussie
     */
    public function delete($identifiant)
    {
        if($this->connection && $this->exist($identifiant)) {
            return ldap_delete($this->connection, sprintf($this->base_identifiant, $identifiant));
        } else {
            return false;
        }
    }

    /**
     * Vérifie l'existence d'une entrée dans le LDAP
     *
     * @param string $identifiant Identifiant de la ressource
     * @return bool La ressource existe
     */
    public function exist($identifiant)
    {
        if($this->connection) {
            if (($search = ldap_search($this->connection, $this->base_dn, $this->id.'='.$identifiant)) !== false) {
                return ldap_count_entries($this->connection, $search) > 0;
            }
            return false;
        }
    }

    /**
     * Déconstructeur
     * Ferme la connection au LDAP
     *
     */
    public function __destruct()
    {
        ldap_unbind($this->connection);
    }
}
