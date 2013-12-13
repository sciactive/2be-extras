<?php
// Financial functions.

class financial {
	// present value of an Annuity
	// uses include defining Remaining Principal of a loan/mortgage
	function PVannuity ($m, $n, $R, $pmt) {
		$Z = 1 / (1 + ($R/$m));
		return ($pmt * $Z * (1 - pow($Z,$n)))/(1 - $Z);
	}

	// Given the compounding, principal, interest rate, you can calculate the monthly payment
	function PaymentCalc ($m, $n, $R, $principal) {
		$Z = 1 / (1 + ($R/$m));
		return ((1 - $Z) * $principal) / ($Z * (1 - pow($Z,$n)));
	}

	// future value of an Annuity
	function FVannuity ($m, $n, $R, $pmt) {
		return $pmt * ((pow((1 + $R/$n),$m) - 1)/($R/$n));
	}

	// present value of a single payment
	function PVsingle ($m, $n, $R, $pmt) {
		return $pmt * pow((1 + $R/$m),-$n);
	}

	// future value of a single payment
	function FVsingle ($m, $n, $R, $pmt) {
		return $pmt * pow((1 + $R/$m),$n);
	}

	// future value of a single payment with continuous compounding
	function FVperp ($m, $n, $R, $pmt) {
		return $pmt * exp($R * ($n/$m));
	}
}