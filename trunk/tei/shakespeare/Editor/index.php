<?php
require 'lightopenid/openid.php';

try
{
	if(!isset($_GET['openid_mode']))
	{
		if(isset($_POST['openid_identifier']) || isset($_POST['Google']))
		{
			$openid = new LightOpenID;

			if (isset($_POST['Google']))
	            $openid->identity = 'https://www.google.com/accounts/o8/id';
			else
				$openid->identity = $_POST['openid_identifier'];

			header('Location: ' . $openid->authUrl());
		}
?>
<form action="" method="post">
    OpenID: <input type="text" name="openid_identifier" /> <button>Login via OpenId</button>
</form>
<form action="" method="post">
	<input type="hidden" name="Google" value="Google" />
    Google: <button>Login via Google</button>
</form>

<?php

	}
	elseif($_GET['openid_mode'] == 'cancel')
	{
		echo 'User has canceled authentication!';
	}
	else
	{
		$openid = new LightOpenID;
		if ($openid->validate())
			header('Location: characteredit.php?idhash=' . sha1($openid->claimed_id));
		else
			echo 'User ' . $openid->identity . ' has not logged in.';
	}
}
catch(ErrorException $e)
{
	echo $e->getMessage();
}
