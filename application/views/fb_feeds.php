<div id="content">
    <div id="fb_feeds_page">        
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <h3 class="page_title">Product Feeds in Catalog</h3>
        <div class="page_tool_bar clearfix">
            <div class="pull-left">
                <div class="btn-group">
                    <a href="<?php echo base_url() . "create-catalog/{$business_id}"; ?>" class="btn btn-primary" id="create_product_catalog_btn">+ Add Product Catalog</a>
                </div>
            </div>
            <div class="pull-right">
                <label class="label-2">Business Accounts: </label>
                <select id="business_accounts" name="business_accounts" class="form-control select2 inline-select" required>
                    <?php
                        foreach ($businesses as $a) {
                            if ($business_id == $a['id']) {
                                echo "<option value='{$a['id']}' selected>{$a['name']}</option>";
                            } else {
                                echo "<option value='{$a['id']}'>{$a['name']}</option>";
                            }
                        }
                    ?>
                </select>
            </div>
        </div>
        <div id="tableResults" class="fb_table_wrap row-fluid">
            <table class="table" id="fb_table">
                <thead>
                    <tr>
                        <th>Catalog Name</th>
                        <th>Feed Name</th>
                        <th>Last Upload</th>
                        <th>Status</th>
                        <th>Next Upload</th>
                        <th>Feed ID</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($catalogs)) : ?>
                        <?php foreach ($catalogs as $catalog) : ?>
                            <?php if (!empty($catalog["product_feeds"])) : ?>
                                <?php foreach ($catalog["product_feeds"] as $row) : ?>
                                    <tr>
                                        <td><a href="https://business.facebook.com/products/catalogs/<?php echo $catalog["id"]; ?>/overview" target="_blank"><?php echo $catalog["name"]; ?></a></td>
                                        <td><?php echo $row["name"]; ?></td>
                                        <td>
                                        <?php
                                            if (!empty($row["latest_upload"]) && isset($row["latest_upload"]["end_time"])) {
                                                //$row["latest_upload"]["end_time"]->setTimezone(new DateTimeZone($timezone));
                                                $output = $row["latest_upload"]["end_time"]->format("M j \a\\t g:i A");
                                                echo $output;
                                                if (!empty($row["schedule"])) {
                                                    echo '<small>Scheduled upload</small>';
                                                } else {
                                                    echo '<small>Single upload</small>';
                                                }
                                            } else {
                                                echo "Uploading..";
                                            }
                                        ?>
                                        </td>
                                        <td>
                                        <?php 
                                            if (!empty($row["latest_upload"])) {
                                                $warnings = 0; $errors = 0;
                                                if (isset($row["latest_upload"]["errors"])) {
                                                    foreach ($row["latest_upload"]["errors"] as $a) {
                                                        if ($a["severity"] == "warning") {
                                                            $warnings++;
                                                        } else if ($a["severity"] == "fatal") {
                                                            $errors++;
                                                        }
                                                    }

                                                    echo $row["product_count"] . " uploaded";
                                                    echo "<small>{$errors} errors, {$warnings} warnings</small>";
                                                } else {
                                                    echo $row["product_count"] . " uploaded";
                                                }
                                            } else {
                                                echo " - ";
                                            }
                                        ?>
                                        </td>
                                        <td>
                                        <?php 
                                            if (!empty($row["schedule"])) {
                                                $dumb = new DateTime();
                                                $dumb->setTimezone(new DateTimeZone($row["schedule"]["timezone"]));
                                                if ($row["schedule"]["interval"] == "WEEKLY") {
                                                    $dumb->modify("next " . ucfirst(strtolower($row["schedule"]["day_of_week"])));
                                                    $dumb->setTime($row["schedule"]["hour"], $row["schedule"]["minute"]);
                                                } else if ($row["schedule"]["interval"] == "DAILY") {
                                                    $dumb->setTime($row["schedule"]["hour"], $row["schedule"]["minute"]);
                                                    if ($dumb < new DateTime()) {
                                                        $dumb->modify("+1 day");
                                                    }
                                                } else if ($row["schedule"]["interval"] == "HOURLY") {
                                                    $dumb->setTime($dumb->format("g"), $row["schedule"]["minute"]);
                                                    if ($dumb < new DateTime()) {
                                                        $dumb->modify("+1 hour");
                                                    }
                                                }

                                                //$dumb->setTimezone(new DateTimeZone($timezone));
                                                echo $dumb->format("M j \a\\t g:i A");
                                                echo '<small>Scheduled ' . ucfirst(strtolower($row["schedule"]["interval"])) . '</small>';
                                            } else {
                                                echo "Single upload";
                                            }
                                        ?>
                                        </td>
                                        <td><?php echo $row["id"]; ?></td>                        
                                        <td class="actions">
                                            
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>                
        </div>
    </div>
</div>

<script>
    table = $("#fb_table").DataTable({                        
                "bLengthChange": true,
                "dom": "lftip",
                "searching": true,                            
                "pageLength": 10,
                "aaSorting": [],
                "aoColumnDefs": [
                    { 'bSortable': false, 'aTargets': [6] },
                ]
    });

    jQuery(document).ready(function($) {
        $("#business_accounts").change(function(e) {
            location.href = base_url + "facebook-feeds/" + $(this).val();
        });
    });
</script>