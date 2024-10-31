<?php 

/**
 * Test RBC Utilities
 * 
 * @author Maritim, Kip
 * @copyright (c) 2021
 */

class Test_Rbc_Utilities extends WP_UnitTestCase {
    
    var $rbc_utilities;
    
    public function setUp(): void {
        parent::setUp();
        
        require_once 'classes/class-rbc-payplan-utilities.php';
        
        $this->rbc_utilities = Rbc_Payplan\Classes\Rbc_Payplan_Utilities::instance();
    }
    
    public function tearDown(): void {
        parent::tearDown();
    }
    
    public function test_properCase() {
        $inputString = 'rbc';
        $output = $this->rbc_utilities->properCase($inputString);
        $this->assertSame('Rbc', $output);
    }
    
    public function test_toBool() {
        //Test Checked
        $inputString = 'yes';
        $output = $this->rbc_utilities->toBool($inputString);
        $this->assertSame( true, $output);
        
        $inputString = 'checked';
        $output = $this->rbc_utilities->toBool($inputString);
        $this->assertSame( true, $output);
        
        $inputString = 'on';
        $output = $this->rbc_utilities->toBool($inputString);
        $this->assertSame( true, $output);
        
        $inputString = 'true';
        $output = $this->rbc_utilities->toBool($inputString);
        $this->assertSame( true, $output);
        
        $inputString = 'yess';
        $output = $this->rbc_utilities->toBool($inputString);
        $this->assertSame( false, $output);
    }
    
    public function test_priceToDollars() {
        $input = 1000;
        $output = $this->rbc_utilities->priceToDollars($input);
        $this->assertSame(10.0, $output);
    }
}