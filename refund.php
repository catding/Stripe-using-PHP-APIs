<!DOCTYPE html>
<html>
<head>
    <title>Refund of payment</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
</head>
<body>

<?php

	 /**
     * This form makes a refund
     */

	require_once('config.php'); // Contains the API key

	if (!empty($_POST))
	{
		// Verify Fields
		if (empty($_POST['email'])) 
		{
            echo "Please enter your email";
        }
	    else if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)==false || strlen($_POST['email']) < 6 || strlen($_POST['email']) > 65)
	    {
	        echo "The email must be between 6 and 65 characters";
	    }
        elseif (empty($_POST['amount'])) 
        {
        	echo "Please enter an amount";
        }
        else // If everything is ok table data is recovered "customer"
        {

		    try
			{
				$bdd = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', '');
			}
			catch (Exception $e)
			{
			        die('Erreur : ' . $e->getMessage());
			}

			
			// Request to retrieve the charge_id and based on the email address entered
			$reponse = $bdd->query("SELECT charge_id, customer_id, date_payment FROM payment WHERE email ='".$_POST['email']."'");
			$donnees = $reponse->fetch();

			// Variable initialization
			$donnees['charge_id'];
			$donnees['customer_id'];
			$donnees['date_payment']; 

			// Ends processing of the request
			$reponse->closeCursor(); 

			
			/* Possible values ​​for $reason (set by Stripe) : duplicate , fraudulent , requested_by_customer. By default set to null */

			if ($_POST['reason'] == null)
			{
				$reason = null;
			}
			else if ($_POST['reason'] == "duplicate")
			{
				$reason = "duplicate";
			}
			else if ($_POST['reason'] == "fraudulent")
			{
				$reason = "fraudulent";
			}
			elseif ($_POST['reason'] == "requested_by_customer") 
			{
				$reason = "requested_by_customer";
			}

			var_dump($donnees['charge_id']);

			// Inserting fields in the db
	        try 
            {
	            $bdd = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', '');
	            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	        } 
	        catch (Exception $e) 
	        {
	            die('Erreur : ' . $e->getMessage());
	        }

	        $req = $bdd->prepare('INSERT INTO refund(charge_id, customer_id, amount, email, reason, date_refund, date_payment) 
	        					  VALUES(:charge_id, :customer_id, :amount, :email, :reason, NOW(), :date_payment)');

	        $req->execute(array(':charge_id' => $donnees['charge_id'],
	        					':customer_id' => $donnees['customer_id'],
                                ':amount' => $_POST['amount'],
	            				':email' => $_POST['email'],
	            				':reason' => $reason,
	            				':date_payment' => $donnees['date_payment']));

			
			// Create the refund
		  	try 
		  	{
				$re = \Stripe\Refund::create(array
					(
						"charge" => $donnees['charge_id'],
						"amount" => $_POST['amount'],
						"reason" => $reason
					));
			} 
			catch(\Stripe\Error\Card $e) 
			{
				  // The refund has been declined
			}

			// Success message
	  		echo '<h1>The refund has been made !</h1>'; 
		}
	}

	echo "	
		<h1>Refund</h1>

		<form action='refund.php' method='post'>

			<input type='email' name='email' placeholder='email' /><br /><br />

			<input type='text' name='amount' placeholder='Amount in cents' /><br /><br />

			<label>Reason for refund :</label> 
			<select name='reason'>
				<option value='null' selected='selected'>-- --</option>
				<option value='duplicate'>Duplicate</option>
				<option value='fraudulent'>Fraudulent</option>
				<option value='requested_by_customer'>Requested by customer</option>
			</select>

			<input type='submit' value='Refund' />

		</form>";
?>

</body>
</html>		