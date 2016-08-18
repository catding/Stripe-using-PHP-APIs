<!DOCTYPE html>
<html>
<head>
    <title>Create a charge</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
</head>
<body>

<?php

	 /**
     * This form make a payment
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
				$bdd = new PDO('mysql:host=mysql.hostinger.fr;dbname=u465173721_strip;charset=utf8', 'u465173721_val', 'spread76140');
			}
			catch (Exception $e)
			{
			        die('Erreur : ' . $e->getMessage());
			}

			// Query to retrieve the customer_id and the token according to the email address entered
			$reponse = $bdd->query("SELECT customer_id, token FROM customer WHERE email ='".$_POST['email']."'");
			$donnees = $reponse->fetch();

			// Variable initialization
			$donnees['customer_id'];
			$donnees['token']; 
			$description = "The payment for the super solution";

			// Ends processing of the request
			$reponse->closeCursor(); 

			// Creation of payment
		  	try 
		  	{
				$charge = \Stripe\Charge::create(array
				(
					"id" => $charge['id'], // ID of payment
				    "amount" => $_POST['amount'], // Amount in cents
				    "currency" => $_POST['devise'],
				    'customer' => $donnees['customer_id'],
				    "description" => $description
			    ));
			} 
			catch(\Stripe\Error\Card $e) 
			{
				  // The card has been declined
			}

			var_dump($charge['id']);

		  	// Success message
		  	echo '<h1>The payment has been made ​​!</h1>'; 

		  	// Inserting fields in the db
	        try 
            {
	            $bdd = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', '');
	        } 
	        catch (Exception $e) 
	        {
	            die('Erreur : ' . $e->getMessage());
	        }

	        $req = $bdd->prepare('INSERT INTO payment(charge_id, customer_id, amount, email, devise, description, date_payment) 
	        					  VALUES(:charge_id, :customer_id, :amount, :email, :devise, :description, NOW())');

	        $req->execute(array(':charge_id' => $charge['id'],
	        					':customer_id' => $donnees['customer_id'],
                                ':amount' => $_POST['amount'],
	            				':email' => $_POST['email'],
	            				':devise' => $_POST['devise'],
	            				':description' => $description));
		}
	}

	echo "	
		<h1>Payment</h1>

		<form action='charge.php' method='post'>

			<input type='email' name='email' placeholder='email' /><br /><br />

			<input type='text' name='amount' placeholder='Amount in cents' /><br /><br />

			<input type='text' name='devise' placeholder='Devise' value='usd' /><br /><br />

			<input type='submit' value='Pay' />

		</form>";
?>

</body>
</html>		

