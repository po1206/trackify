<div id="content">
    <div id="fb_catalogs_page">        
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <h3 class="page_title">Product Catalogs</h3>
        <div class="page_tool_bar clearfix">
            <div class="pull-left">
                <div class="btn-group">
                    <a href="<?php echo base_url() . "create-catalog/{$business_id}"; ?>" class="btn btn-primary" id="create_product_catalog_btn">+ Add Product Catalog</a>
                </div>
            </div>
            <div class="pull-right">
                <label style="margin-right: 10px;">Business Accounts: </label>
                <select id="businesses" name="businesses" class="form-control select2 inline-select" required>
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
                        <th>Last Upload</th>
                        <th>Status</th>
                        <th>Next Upload</th>
                        <th>Feed ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($feeds)) : ?>
                        <?php foreach ($feeds as $row) : ?>
                        <tr>
                            <td><?php echo $row["name"]; ?></td>
                            <td>
                            <?php
                                if (!empty($row["latest_upload"])) {
                                    $row["latest_upload"]["end_time"]->setTimezone(new DateTimeZone($timezone));
                                    $output = $row["latest_upload"]["end_time"]->format("M j \a\\t g:i A");
                                    echo $output;
                                    if (!empty($row["schedule"])) {
                                        echo '<small>Scheduled upload</small>';
                                    } else {
                                        echo '<small>Single upload</small>';
                                    }
                                } else {
                                    echo " - ";
                                }
                            ?>
                            </td>
                            <td>
                            <?php 
                                if (!empty($row["latest_upload"])) {
                                    $warnings = 0; $errors = 0;
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

                                    $dumb->setTimezone(new DateTimeZone($timezone));
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
                    { 'bSortable': false, 'aTargets': [5] },
                ]
    });

    jQuery(document).ready(function($) {
        $("#catalogs").change(function(e) {
            location.href = base_url + "facebook-feeds/" + $(this).val();
        });
    });
</script>