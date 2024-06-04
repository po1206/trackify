<div id="content">
    <div id="custom_audiences_page">        
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <h3 class="page_title">Audiences</h3>
        <?php echo form_open("MY_Facebook/create_LAAs/{$ad_account_id}", array('id' => 'sform')); ?>
            <div class="page_tool_bar clearfix">
                <div class="pull-left">
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true" id="create_audience_btn">Create Audience<span class="caret"></span></button>
                        <ul class="dropdown-menu" aria-labelledby="create_audience_btn">
                            <li>
                            <a class="dropdown-item" href="<?php echo base_url() . "audiences/create/custom/{$ad_account_id}"; ?>">Custom Audience</a>
                            </li>
                            <li>
                            <a class="dropdown-item" href="<?php echo base_url() . "create_audience/lookalike/{$ad_account_id}"; ?>">Lookalike Audience</a>
                            </li>
                        </ul>
                    </div>
                    <button type="submit" name="submit" id="create_laas" class="btn btn-primary">Build LAA's from Selected Base Audiences</a>
                </div>
                <div class="pull-right">
                    <label class="label-2">Ad Account: </label>
                    <select id="ad_accounts" name="ad_accounts" class="form-control select2" required style="margin-left: 10px;">
                        <?php foreach ($ad_accounts as $a) : ?>
                            <?php if ($ad_account_id == $a['adaccount']['id']) : ?>
                                <?php if (!empty($a['pixel_id'])) echo "<option value='{$a['adaccount']['id']}' selected>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>"; ?>
                            <?php else: ?>
                                <?php if (!empty($a['pixel_id'])) echo "<option value='{$a['adaccount']['id']}'>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>"; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="tableResults" class="fb_table_wrap row-fluid">
                <table class="table" id="fb_table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Availability</th>
                            <th>Date Created</th>
                            <th>Audience ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audiences as $row) : ?>
                        <tr>
                            <td><input type="checkbox" name="audience_ids[]" class="audience_id_chk" value="<?php echo $row["id"]; ?>"></td>
                            <td><a href="https://business.facebook.com/ads/manager/audiences/detail/?act=<?php echo str_replace("act_", "", $ad_account_id); ?>&ids=<?php echo $row["id"]; ?>" target="_blank"><?php echo $row["name"]; ?></a></td>
                            <td><?php echo $row["subtype"]; ?></td>
                            <td><?php echo $row["approximate_count"]; ?></td>
                            <td><?php echo $row["short_desc"]; ?><small>Last updated <?php echo $row["last_updated"]; ?></small></td>
                            <td><?php echo $row["time_created"]; ?></td>
                            <td><?php echo $row["id"]; ?></td>
                            <td class="actions">
                                <a style="display: none;" href="<?php echo base_url() . 'MY_Facebook/edit_ca/' . $row['id']; ?>" id="act-<?php echo $row["id"]; ?>" class="fb_edit">Edit</a>
                                <a href="<?php echo base_url() . 'MY_Facebook/delete_ca/' . $ad_account_id . '/' . $row['id']; ?>" id="act-<?php echo $row["id"]; ?>" class="fb_delete">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>                
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
    table = $("#fb_table").DataTable({                        
                "bLengthChange": true,
                "dom": "lftip",
                "searching": true,                            
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
                "aaSorting": [],
                "aoColumnDefs": [
                    { 'bSortable': false, 'aTargets': [0,7] }
                ]
    });

    jQuery(document).ready(function($) {
        $("#ad_accounts").change(function(e) {
            location.href = base_url + "MY_Facebook/custom_audiences/" + $(this).val();
        });

        $("#create_laas").click(function(e) {
            if ($('input[name="audience_ids[]"]:checked').length > 0) {
                
            } else {
                alert("Please select custom audiences.");
                e.preventDefault();
            }
        });
    });
</script>