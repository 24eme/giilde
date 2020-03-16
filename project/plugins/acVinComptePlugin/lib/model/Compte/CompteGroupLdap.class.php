<?php

class CompteGroupLdap extends acVinLdap
{
    public $ou = 'ou=Groups';
    public $id = 'cn';

    private $attributes = [
        'objectClass' => [
            'groupOfUniqueNames',
            'top'
        ]
    ];

    public static $blacklist = [
        '_rouge', '_rose', '_blanc_sec', '_blanc_moelleux', '_blanc_doux', '_blanc'
    ];

    /**
     * Vérifie la présence d'un groupe dans le LDAP, et ajoute
     * un membre s'il n'est pas déjà présent
     *
     * @param string $cn Identifiant du groupe
     * @param string $member Identifiant de l'utilisateur
     * @return bool Succès de l'ajout
     */
    public function saveGroup($cn, $member)
    {
        $fdn = $this->fdn($member);

        if (! $this->exist($cn)) {
            return parent::add($cn, array_merge($this->attributes, ['uniqueMember' => $fdn]));
        }

        if (! $this->memberExists($cn, $fdn)) {
            return $this->addMember($cn, $fdn);
        }
    }

    /**
     * Sauve les groupes du tableau $t pour le compte $compteid
     *
     * @param array $t Tableau de tags
     * @param string $compteid Identifiant du compte
     *
     * @return array $groupes_a_garder La liste des groupes sauvés
     */
    public function saveMultipleGroup(array $t, $compteid)
    {
        $groupes_a_garder = [];

        foreach ($t as $type => $tags) {
            foreach ($tags as $group) {
                $group = $type.'_'.$group;
                $this->saveGroup($group, $compteid);

                // On récupère les groupes
                $groupes_a_garder[] = $group;
            }
        }

        return $groupes_a_garder;
    }

    /**
     * Vérifie si le membre est déjà présent dans le groupe
     *
     * @param string $cn cn du groupe
     * @param string $fdn FDN du membre
     * @return bool Le membre est présent
     */
    public function memberExists($cn, $fdn)
    {
        $search = '(&(objectClass=groupOfUniqueNames)(cn=%s)(uniqueMember=%s))';
        $result = ldap_search(parent::getConnection(),
                            parent::getBaseDN(),
                            sprintf($search, $cn, $fdn),
                            ['dn', 'uniqueMember']
                  );

        return $result && (ldap_count_entries(parent::getConnection(), $result) > 0);
    }

    /**
     * Ajoute un membre au groupe
     *
     * @param string $cn cn du groupe
     * @param string $fdn FDN du membre
     * @return bool Succès de l'ajout
     */
    public function addMember($cn, $fdn)
    {
        return ldap_mod_add(parent::getConnection(),
            sprintf(parent::getBaseIdentifiant(), $cn),
            ['uniqueMember' => $fdn]
        );
    }

    /**
     * Enlève un membre du groupe. Si dernier utilisateur du groupe,
     * alors on supprime directement le groupe.
     *
     * @param string $cn cn du groupe
     * @param string $member DN du membre
     * @return bool Suppression du membre
     */
    public function removeMember($cn, $member)
    {
        $fdn = $this->fdn($member);

        if (! $this->memberExists($cn, $fdn)) {
            return false;
        }

        if ($this->lastMemberOf($cn)) {
            return $this->delete($cn);
        } else {
            return ldap_mod_del(parent::getConnection(),
                sprintf(parent::getBaseIdentifiant(), $cn),
                ['uniqueMember' => $fdn]
            );
        }
    }

    /**
     * Retourne la liste des groupes du membre
     *
     * @param string $member Identifiant du membre
     * @return array Liste des groupes
     */
    public function getMembership($member)
    {
        $fdn = $this->fdn($member);

        $search = '(&(objectClass=groupOfUniqueNames)(uniqueMember=%s))';
        $result = ldap_search(parent::getConnection(),
            parent::getBaseDN(),
            sprintf($search, $fdn),
            ['cn']
        );

        if (! $result) {
            return [];
        }

        if (ldap_count_entries(parent::getConnection(), $result) === 0) {
            return [];
        }

        $entry = ldap_first_entry(parent::getConnection(), $result);
        $g = [];

        do {
            $value = ldap_get_values(parent::getConnection(), $entry, 'cn');
            $g[] = $value[0];
        } while ($entry = ldap_next_entry(parent::getConnection(), $entry));

        return $g;
    }

    /**
     * Vérifie si le groupe n'a pas de membre
     *
     * @param string $cn cn du groupe
     * @return bool Groupe vide
     */
    public function lastMemberOf($cn)
    {
        $count = ldap_read(parent::getConnection(),
            sprintf(parent::getBaseIdentifiant(), $cn),
            '(objectClass=groupOfUniqueNames)',
            ['uniqueMember']
        );

        $entry = ldap_get_entries(parent::getConnection(), $count);
        return $entry[0]['uniquemember']['count'] < 2;
    }

    /**
     * Supprime le groupe du LDAP
     *
     * @param string $cn cn du groupe
     * @return Suppression effectuée
     */
    public function delete($cn)
    {
        return parent::delete($cn);
    }

    /**
     * Créé le FDN de l'utilisateur
     *
     * @param string $member Identifiant du membre
     * @return string FDN du membre
     */
    private function fdn($member)
    {
        return "uid=$member,ou=People,".sfConfig::get('app_ldap_dc');
    }
}
