<?php
  /*
  **  This class holds functions for calculating the ammount of
  **  income tax an employee pays per pay cycle based on the 
  **  following tax brackets reference.
  **  
  **  0 – $18,200             Nil
  **  $18,201 – $37,000       19c for each $1 over $18,200
  **  $37,001 – $80,000       $3,572 plus 32.5c for each $1 over $37,000
  **  $80,001 – $180,000      $17,547 plus 37c for each $1 over $80,000
  **  $180,001 and over       $54,547 plus 45c for each $1 over $180,000
  **
  **  Output is generated at the same location as the input.
  */

  class TaxCalculator
  {
    var $import_file_handle;
  
    /*
    **  Set the import file handle for later
    */
    function __construct($file_handle) {
      $this->import_file_handle = $file_handle;
    }
  
    /*
    **  Open and read the csv file then add it to an array.
    **  Remove the header line from the input csv if it exists.
    */
    function process_data() {
      $employees = array_map('str_getcsv', file($this->import_file_handle));
      if (in_array("employee", $employees[0])) {
        array_shift($employees);
      }
    
      $this->process_output_csv($employees);
    }
  
    /*
    **  Attempt to create the output file if it doesn't exist 
    **  then write to it.  
    */
    function process_output_csv($employees) {
      
      $output_file = preg_replace("%....$%", " - Tax Paid Calculated.csv", $this->import_file_handle);
      $output_handle = fopen($output_file, "w");
      // add header to the created csv.
      fputcsv($output_handle, array("Name", "Taxable Income", "Tax Paid"));

      foreach ($employees as $key => $employee) {
        $name = $employee[0];
        $period = trim($employee[1]);
        $taxable_income = trim($employee[2]);

        $tax_paid = $this->get_income_tax($taxable_income, $period);
        $output = array("$name", "$taxable_income", "$tax_paid");

        fputcsv($output_handle, $output);
      }

      fclose($output_file);
    }
    
    /*
    **  calculate the annual income tax based on which
    **  bracket the employee's salary falls into.  
    */
    function get_income_tax($taxable_income, $period) {
      $annual_period_length = $this->get_pay_period($period);
      $salary = $taxable_income * $annual_period_length;
    
      switch ($salary) {
        case $salary <= 18200:
          $taxed_salary = 0;
          break;
    
        case $salary <= 37000 && $salary >= 18201: 
          $taxed_salary = ($salary - 18200) * 0.19 ;
          break;
    
        case $salary <= 80000 && $salary >= 37001 :
          $taxed_salary = ($salary - 37000) * 0.325 + 3572 ;
          break;
    
        case $salary <= 180000 && $salary >= 80001:
          $taxed_salary = ($salary - 80000) * 0.37 + 17547;
          break;
    
        case $salary >= 180001:
          $taxed_salary = ($salary - 180000) * 0.45 + 54547;
          break;
    
        default:
          echo $salary . " does not fall within a tax bracket.";
          break;
      }
      // calculate how much tax the employee paid per pay period.
      return round($taxed_salary / $annual_period_length, 2);
    }
  
    /*
    ** Convert the textual representation of the pay period to an integer for calculation.
    */
    function get_pay_period($period) {
      $period_map = array(
        'weekly' => 52,
        'fortnightly' => 26,
        'monthly' => 12,
        'yearly' => 1 
      );

      return $period_map[strtolower($period)];
    }
  }
?>