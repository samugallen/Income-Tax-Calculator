<?php
  require_once('include/TaxCalculator.php');
  
  $tax_calculator = new TaxCalculator('data/input.csv');
  $result = $tax_calculator->process_data();
  echo $result;
?>