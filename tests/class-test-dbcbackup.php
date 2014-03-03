<?php
/**
 * MyPlugin Tests
 */
class Dbcbackup_Tests extends WP_UnitTestCase {  
    private $plugin;  
    function setUp() {  
        parent::setUp();  
        $this->plugin = $GLOBALS['dbc-backup-2'];  
    } // end setup  
    function testPluginInitialization() {  
        $this->assertFalse( null == $this->plugin );  
    } // end testPluginInitialization  
} // end class  