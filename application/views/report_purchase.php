<div id="content">
    <div id="report_purchase_page">        
        <?php $this->load->view("blocks/header_message.php"); ?>
        <h3 class="page_title">Purchase Event Logs</h3>
            <div id="report_purchase_table" class="fb_table_wrap row-fluid">
                <table class="table" id="fb_table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Pixel Date</th>
                            <th>Amount</th>
                            <th>Gateway</th>
                            <th>Referrer</th>
                            <th>Campaign</th>
                            <th>Source</th>
                            <th>Medium</th>
                            <th>Term</th>
                            <th>Content</th>
                            <th>Fired?</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
    </div>
</div>

<script>

    jQuery(document).ready(function($) {
        var url = base_url + "Report/ajax_purchase";
        table = $("#fb_table").DataTable({
                    "processing": false,
                    "serverSide": true,
                    "ajax": {
                        "url": url,
                    },
                    "columnDefs": [
                    {
                        "render": function ( data, type, row ) {
                            return data;
                            return data +' ('+ row['adset_id']+')';
                        },
                        "targets": 1
                    }],
                    "lengthMenu": [ 10, 15, 25, 50, 75, 100 ],
                    "searching": false,
                    "ordering": false,
                    "columns": [
                        { "data": "number" },
                        { "data": "updated_at" },
                        { "data": "pixel_date" },
                        { "data": "amount" },
                        { "data": "gateway" },
                        { "data": "referring_site" },
                        { "data": "utm_campaign" },
                        { "data": "utm_source" },
                        { "data": "utm_medium" },
                        { "data": "utm_term" },
                        { "data": "utm_content" },
                        { "data": "fired" },
                    ],
                    "pageLength": 10,
                    "rowCallback": function( row, data, index ) {
                        $('td:eq(0)', row).html( "<a target='_blank' href='https://<?php echo $_SESSION['shop']; ?>/admin/orders/" + data['id'] + "'>#" + (data["number"] + 1000) + "</a>" );
                        if (data["fired"] == 1) {
                            $('td:eq(11)', row).html('<span class="fb-icon-ok glyphicon glyphicon-ok" aria-hidden="true"></span>');
                        } else {
                            $('td:eq(11)', row).html('<span class="fb-icon-remove glyphicon glyphicon-remove" aria-hidden="true"></span>');
                        }
                    },
                    "drawCallback": function( data ) {
                        if (data.json.error) {
                            alert(data.json.message);
                        }
                    },
                    "aaSorting": []

        });
    });
</script>