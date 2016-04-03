<div class="wrap">
  <form method="post" accept-charset="utf-8">

    <h2>Process Image After Upload</h2>

    <div style="max-width:700px">
      <p>This plugin automatically correct the levels of an image, and also apply a sharpen filter.</p>
      <p>Only JPG and PNG images are supported at the moment.</p>
      <p><strong>Note:</strong> the process will discard the original uploaded file including EXIF data.</p>
    </div>

    <hr style="margin-top:20px; margin-bottom:0;">
    <hr style="margin-top:1px; margin-bottom:40px;">

    <h3>Post-Upload Image Processing Options</h3>
    <table class="form-table">

      <!-- Auto Levels Correction -->
      <tr>
        <th scope="row">Enable Auto Levels Correction</th>
        <td valign="top">
          <select name="yesno" id="yesno">
            <option value="no" label="no" <?php echo ($autolevels_enabled == 'no') ? 'selected="selected"' : ''; ?>>NO</option>
            <option value="yes" label="yes" <?php echo ($autolevels_enabled == 'yes') ? 'selected="selected"' : ''; ?>>YES</option>
          </select>
        </td>
      </tr>
      <!--/ Auto Levels Correction -->

      <!-- Sharpen -->
      <tr>
        <th scope="row">Enable Sharpen</th>
        <td valign="top">
          <select name="sharpen" id="sharpen">
            <option value="no" label="no" <?php echo ($sharpen_enabled == 'no') ? 'selected="selected"' : ''; ?>>NO</option>
            <option value="yes" label="yes" <?php echo ($sharpen_enabled == 'yes') ? 'selected="selected"' : ''; ?>>YES</option>
          </select>
        </td>
      </tr>
      <!--/ Sharpen -->

      <!-- 
      <tr>
        <th scope="row">Max image dimensions</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Maximum width and height</span></legend>
            <label for="maxwidth">Max width</label>
            <input name="maxwidth" step="1" min="0" id="maxwidth" class="small-text" type="number" value="<?php echo $max_width; ?>">
            &nbsp;&nbsp;&nbsp;<label for="maxheight">Max height</label>
          </fieldset>
        </td>
      </tr>

      <tr>
        <th scope="row">JPEG compression level</th>
        <td valign="top">
          <select id="quality" name="quality">
          <?php for($i=1; $i<=100; $i++) : ?>
            <option value="<?php echo $i; ?>" <?php if($compression_level == $i) : ?>selected<?php endif; ?>><?php echo $i; ?></option>
          <?php endfor; ?>
          </select>
          <p class="description"><code>1</code> = low quality (smallest files)
          <br><code>100</code> = best quality (largest files)
          <br>Recommended value: <code>90</code></p>
        </td>
      </tr>
      -->

    </table>

    <p class="submit" style="margin-top:10px;border-top:1px solid #eee;padding-top:20px;">
      <input type="hidden" id="convert-bmp" name="convertbmp" value="no" />
      <input type="hidden" name="action" value="update" />
      <input id="submit" name="ple-piau_options_update" class="button button-primary" type="submit" value="Update Options">
    </p>

  </form>

    <hr style="margin-top:30px;">

    <!-- Donations -->
    <!--
    <div style="max-width:700px">
      <h4 style="font-size: 15px;font-weight: bold;margin: 2em 0 0;">Like the plugin?</h4>
      <p>PayPal</p>
      <p>BitCoin (coinbase)</p>
    </div>
    -->
    <!--/ Donations -->

</div>