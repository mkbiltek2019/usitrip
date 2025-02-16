<?php
// this file was created by Clifton Murphy <blue_glowstick@yahoo.com>
// using code supplied in a previous release by Thomas Nordstrom <t_nordstrom@yahoo.com>
// I take no credit for any of this work. I simply created this file and
// recompiled the distribution zip file with the fixes to prevent parse errors.
/*
  $Id: cvs_help.php,v 1.3 2004/02/4 07:28:00 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<title>Card Verification Value (CVV) Help</title>
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<style type="text/css"><!--
BODY { margin-bottom: 5px; margin-left: 5px; margin-right: 5px; margin-top: 5px; }
table.cvvhelp { background: #ffffff; border: 1px solid red;}
//--></style>
<body marginwidth="5" marginheight="5" topmargin="5" bottommargin="5" leftmargin="5" rightmargin="5">
<table class="cvvhelp" width="100%" border="0" cellspacing="5" cellpadding="0">
  <tr>
    <td colspan="2"><p><strong>What is a Card Verification Value (CVV)?</strong>
      </p>
      <p>The Card Verification Value (CVV) is a 3-digit number found on the signature
        panel on the back of your credit card. It is an additional safeguard that
        helps us validate your purchase and protect against fraud. It is not contained
        in the magnetic stripe information and is therefore not printed on sales
        receipts.</p>
      <p><strong>Where is the CVV located?</strong></p>
      </td>
  </tr>
  <tr>
    <td><p><strong>VISA, MASTERCARD &amp; DISCOVERY</strong><br>
        You can find your card verification code on the reverse side of your credit
        card, printed into the signature field. It is a 3-digit group for MasterCard,
        Visa, and Discovery.<br>
      </p></td>
    <td><img src="images/cvm_help1.jpg" width="200" height="139"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><p><strong>AMERICAN EXPRESS</strong><br>
        The American Express Card verification code is a 4-digit number printed
        on the front of your card. It appears after and to the right (above) of
        your card number.<br>
      </p></td>
    <td><img src="images/cvm_help2.jpg" width="200" height="139"></td>
  </tr>
</table>
<p class="smallText" align="right"><?php echo '<a href="javascript:window.close()">' . CLOSE_WINDOW . '</a>'; ?></p>

</body>
</html>
<?php require('includes/application_bottom.php'); ?>
