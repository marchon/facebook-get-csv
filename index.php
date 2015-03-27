<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Facebook data</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
    
</head>
<body>
    <div class="container">
        <form class="form-main" id="facebook-form" data-toggle="validator" method="POST" action='fbproc.php'>
            <h2 class="form-main-heading">Enter facebook URL to get data</h2>
            
            <?php for ($i=1; $i<=10; $i++) { ?>
                <div class="form-group">
                    <input class="form-control" name="facebook-url[]" type="text" id="facebook-url-<?= $i ?>" placeholder="Facebook URL" data-error="Please insert correct URL" pattern="^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})(\/[\d\w.?=]+)?$" required <?= ($i == 1) ? 'autofocus' : '' ?>/>
                    <span class="help-block with-errors"></span>
                </div>
            <?php } ?>
            <button type="submit" class="btn btn-primary">Get info</button>
        </form>
        <br/>
        <div class="alert alert-danger" id="error-message" role="alert"></div>

        <div id="result">
            <h3 class="form-main-heading">Facebook data</h3>
            <label>Page ID:</label><span id="fb-id"></span><br/>
            <label>Page name:</label><span id="fb-name"></span><br/>
            <label>Rss Feed URL:</label><span id="fb-rss"></span><br/>
            <label>Page URL:</label><span id="fb-page"></span><br/>
        </div>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>    
    <!-- Bootstrap validator -->
    <script src="components/bootstrap-validator/js/validator.js"></script>
    <!-- AJAX -->
    <script>
        $(document).ready(function() {
            
            // Hide error message
            $('#error-message').hide();
            $('#result').hide();
            
            // Process form submission
            $("#facebook-form").validator();
        
            // Make sure there's protocol inserted in URL
            $('input[name^=facebook-url]').change(function() {
                var url = $(this).val();
                $(this).val(addProtocol(url)); 
            });
            
        });
        
        // Add 'http://' to the url if it misses that
        function addProtocol(url) {
            if (url.search(/^http[s]?\:\/\//) == -1) {
                url = 'http://' + url;
            }            
            return url;
        }
    </script>
</body>
</html>


