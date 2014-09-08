<?php

class CNetIDChecker{
    static function check_cnetid( $cnetid ) {
        $ds = ldap_connect("ldap.uchicago.edu");
        $dn = "ou=people,dc=uchicago,dc=edu";
        $filter = "(&(objectclass=inetOrgPerson)(uid=$cnetid))";
        $search = ldap_search($ds, $dn, $filter, array("mail") );

        if ( ldap_count_entries($ds,$search) != 1 ) {
            # do some error checking
            return false;
        }
        $entry = ldap_first_entry($ds,$search);
        list($email) = ldap_get_values($ds,$entry,'mail');
        return $email;
    }

    static function check_cnetid_pi( $cnetid ) {
        $ds = ldap_connect("ldap.rcc.uchicago.edu");
        $dn = "ou=group,dc=rcc,dc=uchicago,dc=edu";
        $filter = "(cn=pi-$cnetid)";
        $search = ldap_search($ds, $dn, $filter, array("cn") );
        if ( ldap_count_entries($ds,$search) != 1 ) {
            return false;
        } else {
            return true;
        }
    }
}

