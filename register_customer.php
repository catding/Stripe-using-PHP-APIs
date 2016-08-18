<?php

    /**
     * This form creates a client in the Stripe dashboard and checks whether the payment information is correct
     */

	require 'init.php';
    require_once('config.php'); // Contains the API key

	if (!empty($_POST)) 
    {
        $error = '';
        $success = '';

        try 
        {
            if (empty($_POST['street']) || empty($_POST['city']) || empty($_POST['zip']))
            {
                throw new Exception("Fill out all required fields.");
            } 
            
            if (!isset($_POST['stripeToken']))
            {
                throw new Exception("The Stripe Token was not generated correctly");
            }
            
            // Get the credit card data through the token
            $token  = $_POST['stripeToken'];

            
            // Creating the client
            $customer = \Stripe\Customer::create(array
            (
                "description" => $_POST['name']." ".$_POST['surname'],
                "source" => $token, // Get by Stripe.js
                "email" => $_POST['email'],
                "id" => $customer->id
            ));

            $success = '<div class="alert alert-success">
                            <strong>Success!</strong> Your registration was successful.
                        </div>';                    
        }
        catch (Exception $e) 
        {
            $error = '<div class="alert alert-danger">
                        <strong>Error!</strong> ' . $e->getMessage() . '
                      </div>';
        }

        if (!isset($_POST['name']) || empty($_POST['name'])) {
            echo "The name is required and cannot be empty";
        }
        else if (!isset($_POST['surname']) || empty($_POST['surname'])) {
            echo "The surname is required and cannot be empty";
        }
	    else if (!isset($_POST['street']) || empty($_POST['street'])) {
	        echo "The street is required and cannot be empty";
	    }
	    else if (!empty($_POST['street']) && (strlen($_POST['street']) < 6 || strlen($_POST['street']) > 96)) {
	        echo "The street must be more than 6 and less than 96 characters long";
	    }
	    else if (!isset($_POST['city']) || empty($_POST['city'])) {
	        echo "The city is required and cannot be empty";
	    }
	    else if (!isset($_POST['zip']) || empty($_POST['zip'])) {
	        echo "The zip is required and cannot be empty";
	    }
	    else if (strlen($_POST['zip']) < 3 || strlen($_POST['zip']) > 9) {
	        echo "The zip must be more than 3 and less than 9 characters long";
	    }
	    else if (!isset($_POST['country']) || empty($_POST['country'])) {
	        echo "The country is required and cannot be empty";
	    }
	    else if (!isset($_POST['email']) || empty($_POST['email'])) {
	        echo "Your email is required and cannot be empty";
	    }
	    else if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)==false || strlen($_POST['email']) < 6 || strlen($_POST['email']) > 65){
	        echo "The email must be more than 6 and less than 65 characters long65";
	    }
	    else if (!isset($_POST['stripeToken']) || empty($_POST['stripeToken'])) {
	        echo "The token is empty! Try again or Contact administrator";
	    }
	    else if (strlen($_POST['stripeToken']) != 28) {
	        echo "The token is invalid! Try again or Contact administrator";
	    }
	    else 
        {
            // Get the data entered in variable
            $name = $_POST['name'];
            $surname = $_POST['surname'];
	        $street = $_POST['street'];
	        $city = $_POST['city'];
	        $state = $_POST['state'];
	        $zip = $_POST['zip'];
	        $country = $_POST['country'];
	        $email = $_POST['email'];
	        $token = $_POST['stripeToken'];

	        
            // Insert fields in the db
	        try 
            {
	            $bdd = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', '');
	        } 
            catch (Exception $e) 
            {
	            die('Erreur : ' . $e->getMessage());
	        }

	        $req = $bdd->prepare('INSERT INTO customer(customer_id, name, surname, street, city, state, zip, country, email, token, date_registration) 
                                  VALUES(:customer_id, :name, :surname, :street, :city, :state, :zip, :country, :email, :token, NOW())');
	        $req->execute(array(':customer_id' => $customer['id'],
                                ':name' => $name,
                                ':surname' => $surname,
                                ':street' => $street,
	          					':city' => $city,
	           					':state' => $state,
	            				':zip' => $zip,
	           					':country' => $country,
	            				':email' => $email,
	           					':token' => $token));

            echo "Your payment information have been saved";

	    }
	}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Payment Form</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-formhelpers-min.css" media="screen">
    <link rel="stylesheet" href="css/bootstrapValidator-min.css"/>
    <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css"/>
    <link rel="stylesheet" href="css/bootstrap-side-notes.css"/>
    <style type="text/css">
        .col-centered {
            display: inline-block;
            float: none;
            text-align: left;
            margin-right: -4px;
        }

        .row-centered {
            margin-left: 9px;
            margin-right: 9px;
        }

        body {
            padding-top: 50px;
        }
    </style>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/bootstrap-min.js"></script>
    <script src="js/bootstrap-formhelpers-min.js"></script>
    <script type="text/javascript" src="js/bootstrapValidator-min.js"></script>
    <script src="js/jquery.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Stripe.setPublishableKey('pk_test_Hp7t5hK9mGjJuwcWiC02vmwH');
            $('#stripe').bootstrapValidator({
                message: 'This value is not valid',
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                submitHandler: function (validator, form, submitButton) {
                    // createToken returns immediately - the supplied callback submits the form if there are no errors
                    Stripe.card.createToken({
                        number: $('.card-number').val(),
                        cvc: $('.card-cvc').val(),
                        exp_month: $('.card-expiry-month').val(),
                        exp_year: $('.card-expiry-year').val(),
                        name: $('.card-holder-name').val(),
                        address_line1: $('.address').val(),
                        address_city: $('.city').val(),
                        address_zip: $('.zip').val(),
                        address_state: $('.state').val(),
                        address_country: $('.country').val()
                    }, stripeResponseHandler);
                    return false; // submit from callback
                },
                fields: {
                    street: {
                        validators: {
                            notEmpty: {
                                message: 'The street is required and cannot be empty'
                            },
                            stringLength: {
                                min: 6,
                                max: 96,
                                message: 'The street must be more than 6 and less than 96 characters long'
                            }
                        }
                    },
                    city: {
                        validators: {
                            notEmpty: {
                                message: 'The city is required and cannot be empty'
                            }
                        }
                    },
                    zip: {
                        validators: {
                            notEmpty: {
                                message: 'The zip is required and cannot be empty'
                            },
                            stringLength: {
                                min: 3,
                                max: 9,
                                message: 'The zip must be more than 3 and less than 9 characters long'
                            }
                        }
                    },
                    email: {
                        validators: {
                            notEmpty: {
                                message: 'The email address is required and can\'t be empty'
                            },
                            emailAddress: {
                                message: 'The input is not a valid email address'
                            },
                            stringLength: {
                                min: 6,
                                max: 65,
                                message: 'The email must be more than 6 and less than 65 characters long'
                            }
                        }
                    },
                    cardholdername: {
                        validators: {
                            notEmpty: {
                                message: 'The card holder name is required and can\'t be empty'
                            },
                            stringLength: {
                                min: 6,
                                max: 70,
                                message: 'The card holder name must be more than 6 and less than 70 characters long'
                            }
                        }
                    },
                    cardnumber: {
                        selector: '#cardnumber',
                        validators: {
                            notEmpty: {
                                message: 'The credit card number is required and can\'t be empty'
                            },
                            creditCard: {
                                message: 'The credit card number is invalid'
                            },
                        }
                    },
                    expMonth: {
                        selector: '[data-stripe="exp-month"]',
                        validators: {
                            notEmpty: {
                                message: 'The expiration month is required'
                            },
                            digits: {
                                message: 'The expiration month can contain digits only'
                            },
                            callback: {
                                message: 'Expired',
                                callback: function (value, validator) {
                                    value = parseInt(value, 10);
                                    var year = validator.getFieldElements('expYear').val(),
                                        currentMonth = new Date().getMonth() + 1,
                                        currentYear = new Date().getFullYear();
                                    if (value < 0 || value > 12) {
                                        return false;
                                    }
                                    if (year == '') {
                                        return true;
                                    }
                                    year = parseInt(year, 10);
                                    if (year > currentYear || (year == currentYear && value > currentMonth)) {
                                        validator.updateStatus('expYear', 'VALID');
                                        return true;
                                    } else {
                                        return false;
                                    }
                                }
                            }
                        }
                    },
                    expYear: {
                        selector: '[data-stripe="exp-year"]',
                        validators: {
                            notEmpty: {
                                message: 'The expiration year is required'
                            },
                            digits: {
                                message: 'The expiration year can contain digits only'
                            },
                            callback: {
                                message: 'Expired',
                                callback: function (value, validator) {
                                    value = parseInt(value, 10);
                                    var month = validator.getFieldElements('expMonth').val(),
                                        currentMonth = new Date().getMonth() + 1,
                                        currentYear = new Date().getFullYear();
                                    if (value < currentYear || value > currentYear + 100) {
                                        return false;
                                    }
                                    if (month == '') {
                                        return false;
                                    }
                                    month = parseInt(month, 10);
                                    if (value > currentYear || (value == currentYear && month > currentMonth)) {
                                        validator.updateStatus('expMonth', 'VALID');
                                        return true;
                                    } else {
                                        return false;
                                    }
                                }
                            }
                        }
                    },
                    cvv: {
                        selector: '#cvv',
                        validators: {
                            notEmpty: {
                                message: 'The cvv is required and can\'t be empty'
                            },
                            cvv: {
                                message: 'The value is not a valid CVV',
                                creditCardField: 'cardnumber'
                            }
                        }
                    },
                }
            });
        });
    </script>
    <script type="text/javascript">
        // this identifies your website in the createToken call below
        Stripe.setPublishableKey('pk_test_Hp7t5hK9mGjJuwcWiC02vmwH');

        function stripeResponseHandler(status, response) {
            if (response.error) {
                // re-enable the submit button
                $('.submit-button').removeAttr("disabled");
                // show hidden div
                document.getElementById('a_x200').style.display = 'block';
                // show the errors on the form
                $(".payment-errors").html(response.error.message);
            } else {
                var form$ = $("#stripe");
                // token contains id, last4, and card type
                var token = response['id'];
                // insert the token into the form so it gets submitted to the server
                form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
                // and submit
                form$.get(0).submit();
            }
        }
    </script>
</head>
<body>

<form action="register_customer.php" method="POST" id="stripe" class="form-horizontal"> <!-- style="display:none;" -->
    <div class="row row-centered">
        <div class="col-md-4 col-md-offset-4">
            <div class="page-header">
                <h2 class="gdfg">Secure Payment Form</h2>
            </div>
            <noscript>
                <div class="bs-callout bs-callout-danger">
                    <h4>JavaScript is not enabled!</h4>
                    <p>This payment form requires your browser to have JavaScript enabled. Please activate JavaScript
                        and reload this page. Check <a href="http://enable-javascript.com" target="_blank">enable-javascript.com</a>
                        for more informations.</p>
                </div>
            </noscript>

            <div class="alert alert-danger" id="a_x200" style="display: none;">
                <strong>Error!</strong>
                <span class="payment-errors"></span>
            </div>
            <span class="payment-success">
              <?php $success ?>
              <?php $error ?>
            </span>
            <fieldset>
                <!-- Form Name -->
                <legend>Billing Details</legend>

                <div class="form-group">
                    <label class="col-sm-4 control-label" for="name">Name</label>
                    <div class="col-sm-6">
                        <input type="text" id="name" name="name" placeholder="Name"
                               class="name form-control" <?php echo (isset($_POST['name'])) ? "value='{$_POST['name']}'" : "" ?>>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label" for="name">Surname</label>
                    <div class="col-sm-6">
                        <input type="text" id="surname" name="surname" placeholder="Surname"
                               class="surname form-control" <?php echo (isset($_POST['surname'])) ? "value='{$_POST['surname']}'" : "" ?>>
                    </div>
                </div>

                <!-- Street -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="street">Street</label>
                    <div class="col-sm-6">
                        <input type="text" id="street" name="street" placeholder="Street"
                               class="address form-control" <?php echo (isset($_POST['street'])) ? "value='{$_POST['street']}'" : "" ?>>
                    </div>
                </div>

                <!-- City -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="city">City</label>
                    <div class="col-sm-6">
                        <input type="text" id="city" name="city" placeholder="City"
                               class="city form-control" <?php echo (isset($_POST['city'])) ? "value='{$_POST['city']}'" : "" ?>>
                    </div>
                </div>

                <!-- State -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="state">State</label>
                    <div class="col-sm-6">
                        <input type="text" id="state" name="state" maxlength="65" placeholder="State"
                               class="state form-control" <?php echo (isset($_POST['state'])) ? "value='{$_POST['state']}'" : "" ?>>
                    </div>
                </div>

                <!-- Postcal Code -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="cp">Postal Code</label>
                    <div class="col-sm-6">
                        <input type="text" id="cp" name="zip" maxlength="9" placeholder="Postal Code"
                               class="zip form-control" <?php echo (isset($_POST['zip'])) ? "value='{$_POST['zip']}'" : "" ?>>
                    </div>
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="country">Country</label>
                    <div class="col-sm-6">
                        <input type="text" id="country" name="country" placeholder="Country"
                               class="country form-control" <?php echo (isset($_POST['country'])) ? "value='{$_POST['country']}'" : "" ?>>
                        <!--<div class="country bfh-selectbox bfh-countries" name="country" placeholder="Select Country" data-flags="true" data-filter="true"> </div>-->
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="email">Email</label>
                    <div class="col-sm-6">
                        <input type="text" id="email" name="email" maxlength="65" placeholder="Email"
                               class="email form-control" <?php echo (isset($_POST['email'])) ? "value='{$_POST['email']}'" : "" ?>>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Card Details</legend>

                <!-- Card Holder Name -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="cardName">Card Holder's Name</label>
                    <div class="col-sm-6">
                        <input type="text" id="cardName" name="cardholdername" maxlength="70"
                               placeholder="Card Holder Name"
                               class="card-holder-name form-control" <?php echo (isset($_POST['cardholdername'])) ? "value='{$_POST['cardholdername']}'" : "" ?>>
                    </div>
                </div>

                <!-- Card Number -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="cardNumber">Card Number</label>
                    <div class="col-sm-6">
                        <input type="text" id="cardNumber" name="cardnumber" maxlength="19" placeholder="Card Number"
                               class="card-number form-control" value="4242424242424242">
                        <span id="controlCardNumber"></span>
                    </div>
                </div>

                <!-- Expiry-->
                <div class="form-group">
                    <label class="col-sm-4 control-label">Card Expiry Date</label>
                    <div class="col-sm-6">
                        <div class="form-inline">
                            <select name="select1" id="expMonth" data-stripe="exp-month"
                                    class="card-expiry-month stripe-sensitive required form-control">
                                <option value="01" selected="selected">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                                <option value="08">08</option>
                                <option value="09">09</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12" selected>12</option>
                            </select>
                            <span> / </span>
                            <select name="select2" id="expYear" data-stripe="exp-year"
                                    class="card-expiry-year stripe-sensitive required form-control">
                            </select>
                            <script type="text/javascript">
                                var select = $(".card-expiry-year"),
                                    year = 2017;//new Date().getFullYear();

                                for (var i = 0; i < 12; i++) {
                                    select.append($("<option value='" + (i + year) + "' " + (i === 0 ? "selected" : "") + ">" + (i + year) + "</option>"))
                                }
                            </script>
                        </div>
                    </div>
                </div>

                <!-- CVV -->
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="cvv">CVV/CVV2</label>
                    <div class="col-sm-3">
                        <input type="text" id="cvv" placeholder="CVV" name="cvv" maxlength="4"
                               class="card-cvc form-control" value="123">
                    </div>
                </div>

                <!-- Important notice -->
                <div class="form-group">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">Important notice</h3>
                        </div>
                        <div class="panel-body">
                            <p>Your card will be registred in Stripe after submit.</p>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="control-group">
                        <div class="controls">
                            <center>
                                <button class="btn btn-success" type="submit">Enregistrer</button>
                            </center>
                        </div>
                    </div>
            </fieldset>
        </div>
    </div>
</form>

<script type="text/javascript">
    (function ($) {
        var $cardNumber = "4242424242424242";
        var $cardExpMonth = 12;
        var $cardExpYear = 2017;
        var $cardCvv = '123';
        var $validCard = isValidCardNumber($cardNumber)
        var $notif = {"icon": "success", "color": "green"};
        if ($validCard == "errors") {
            $notif = {"icon": "errors", "color": "red"};
        }
        $('#controlCardNumber').html("<span style='color: " + $notif.color + ";'><i class='fa fa-" + $notif.icon + "'></i><small>" + $validCard + "</small></span>")

        $('#cardNumber').on('keyup', function () {
            $cardNumber = $(this).val();
            $validCard = isValidCardNumber($cardNumber)
            $('#controlCardNumber').html("<span style='color: " + $notif.color + ";'><i class='fa fa-" + $notif.icon + "'></i><small>" + $validCard + "</small></span>")
        });
        function isValidCardNumber(cardNumber) {
            var visa = /^4[0-9]{12}(?:[0-9]{3})?$/;
            var masterCard = /^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/;
            var americanExpress = /^3[47][0-9]{13}$/;
            var dinersClub = /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/;
            var discover = /^6(?:011|5[0-9]{2})[0-9]{12}$/;
            var jcb = /^(?:2131|1800|35\d{3})\d{11}$/;

            if (visa.test(cardNumber)) {
                return 'visa';
            } else if (masterCard.test(cardNumber)) {
                return 'masterCard';
            }
            else if (americanExpress.test(cardNumber)) {
                return 'americanExpress';
            } else if (dinersClub.test(cardNumber)) {
                return 'dinersClub';
            }
            else if (discover.test(cardNumber)) {
                return 'discover';
            } else if (jcb.test(cardNumber)) {
                return 'jcb';
            }

            return 'errors';
        };

        $('#expMonth').on('keyup', function () {
            $cardExpMonth = $(this).val();
        });
        $('#expYear').on('keyup', function () {
            $cardExpYear = $(this).val();
        });
        $('#cvv').on('keyup', function () {
            $cardCvv = $(this).val();
        });

        Stripe.card.createToken({
            number: $cardNumber,
            exp_month: $cardExpMonth,
            exp_year: $cardExpYear,
            cvc: $cardCvv
        }, function (status, response) {
            if(response.id==undefined){
                alert('token invalid');
            }
            else {
                $('form#stripe').append("<input type='hidden' name='stripeToken' value='" + response.id + "' />");
            }
        });
    })(jQuery);
</script>

</body>
</html>