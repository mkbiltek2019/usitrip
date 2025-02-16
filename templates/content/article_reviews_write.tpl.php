<?php 
$titletagstr = HEADING_TITLE . $articles_name;
echo tep_get_design_body_header($titletagstr); ?>
<?php echo tep_draw_form('article_reviews_write', tep_href_link(FILENAME_ARTICLE_REVIEWS_WRITE, 'action=process&amp;articles_id=' . $HTTP_GET_VARS['articles_id']), 'post', 'onSubmit="return checkForm();"'); ?>
<table border="0" width="100%" cellspacing="0" cellpadding="<?php echo CELLPADDING_SUB; ?>">
<?php
  if ($messageStack->size('review') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('review'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>
<?php
// BOF: Lango Added for template MOD
if (MAIN_TABLE_BORDER == 'yes'){
table_image_border_top(false, false, $header_text);
}
// EOF: Lango Added for template MOD
?>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" colspan="2"><?php echo '<b>' . SUB_TITLE_FROM . '</b> ' . tep_output_string_protected($customer['customers_firstname'] . ' ' . $customer['customers_lastname']); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo SUB_TITLE_REVIEW; ?></b></td>
                <td align="right" class="main"><?php echo TEXT_APPROVAL_WARNING; ?></td>
              </tr>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
                  <tr class="infoBoxContents">
                    <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
                      <tr> 
                                        <td width="23%" class="main"><b>Your Name:</b>&nbsp;</td>
                                        <td colspan="2" class="main" id="customers_name"><?php echo tep_draw_input_field('customers_name',tep_output_string_protected($customer['customers_firstname'] . ' ' . $customer['customers_lastname'])); ?>&nbsp;<font color="#FF0000">(required)</font></td>
                                      </tr>
					  <tr>
                        <td class="main" colspan="2"><?php echo tep_draw_textarea_field('review', 'soft', 60, 15); ?></td>
                      </tr>
                      <tr>
                        <td class="main" colspan="2"><?php echo '<b>' . SUB_TITLE_RATING . '</b> ' . TEXT_BAD . ' ' . tep_draw_radio_field('rating', '1') . ' ' . tep_draw_radio_field('rating', '2') . ' ' . tep_draw_radio_field('rating', '3') . ' ' . tep_draw_radio_field('rating', '4') . ' ' . tep_draw_radio_field('rating', '5') . ' ' . TEXT_GOOD; ?></td>
                      </tr>
                    </table></td>
                  </tr>
                </table></td>
              </tr>
      </table></td>
           </tr>
    </table></td>
      </tr>
<?php
// BOF: Lango Added for template MOD
if (MAIN_TABLE_BORDER == 'yes'){
table_image_border_bottom();
}
// EOF: Lango Added for template MOD
?>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
                  <tr class="infoBoxContents">
                    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_ARTICLE_REVIEWS, tep_get_all_get_params(array('reviews_id', 'action'))) . '">' . tep_template_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
                        <td class="main" align="right"><?php echo tep_template_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
                    </table></td>
                  </tr>
                </table></td>
              </tr>

    </table></form>

<?php echo tep_get_design_body_footer();?>