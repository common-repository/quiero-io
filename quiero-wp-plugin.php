<?php

/**
 * Plugin Name: Quiero - Making Sense of Engagement
 * Plugin URI: https://quiero.io/getapp
 * Description: The ideal plugin for advanced remarketing in one click, split your remarketing pixel by behavior parameters to achieve better ROAS.
 * Version: 2.0.1
 * Author: Quiero
 * Author URI: https://Quiero.io/
 **/

define('QRO_PLUGIN_DIR', str_replace('\\', '/', dirname(__FILE__)));

if (!class_exists('QuieroPixelScript')) {

    class QuieroPixelScript
    {

        function __construct()
        {
            add_action('init', array(&$this, 'qro_init'));
            add_action('admin_init', array(&$this, 'qro_admin_init'));
            add_action('admin_menu', array(&$this, 'qro_admin_menu'));
            add_action('wp_head', array(&$this, 'qro_wp_head'));
            add_action('admin_print_styles', array(&$this, 'qro_load_admin_style'));

            register_activation_hook(__FILE__, array(&$this, 'qro_webhook_activated'));
            register_deactivation_hook(__FILE__, array(&$this, 'qro_webhook_deactivated'));
        }

        function qro_init()
        {
            load_plugin_textdomain('insert-quiero-pixel', false, dirname(plugin_basename(__FILE__)) . '/lang');
        }

        function qro_admin_init()
        {
            register_setting('insert-quiero-pixel', 'cb_fb', 'trim');
            register_setting('insert-quiero-pixel', 'cb_ga', 'trim');
            register_setting('insert-quiero-pixel', 'cb_pn', 'trim');
            register_setting('insert-quiero-pixel', 'cb_bi', 'trim');

            register_setting('insert-quiero-pixel', 'cb_time', 'trim');
            register_setting('insert-quiero-pixel', 'cb_sd', 'trim');
            register_setting('insert-quiero-pixel', 'cb_pv', 'trim');

            register_setting('insert-quiero-pixel', 'ces_range', 'trim');

            register_setting('insert-quiero-pixel',  'has_changes', 'trim');

            // register_setting('insert-quiero-pixel',  'pro_code', 'trim');
        }

        function qro_webhook_activated()
        {
            register_uninstall_hook(__FILE__, array(&$this, 'qro_on_uninstall'));

            $user = wp_get_current_user()->user_email;
            $site = get_site_url();
            wp_remote_get('https://hook.integromat.com/8jawa6bpvyl4d8b2mbbxgh1xpixtpa7w?event=activated&user=' . $user . '&site=' . $site);
        }
        function qro_webhook_deactivated()
        {
            $user = wp_get_current_user()->user_email;
            $site = get_site_url();
            wp_remote_get('https://hook.integromat.com/8jawa6bpvyl4d8b2mbbxgh1xpixtpa7w?event=deactivated&user=' . $user . '&site=' . $site);
        }
        function qro_on_uninstall()
        {
            // write your uninstall code
            $user = wp_get_current_user()->user_email;
            $site = get_site_url();
            wp_remote_get('https://hook.integromat.com/8jawa6bpvyl4d8b2mbbxgh1xpixtpa7w?event=uninstalled&user=' . $user . '&site=' . $site);
        }


        function qro_load_admin_style()
        {
            $current_screen = get_current_screen();
            if (strpos($current_screen->base, 'qro-')) {
                wp_enqueue_style('qro_admin_css', plugins_url('qro_admin.css', __FILE__));
            }
        }

        function qro_admin_menu()
        {

            $svg_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMzJweCIgaGVpZ2h0PSIzMnB4IiB2aWV3Qm94PSIwIDAgMzIgMzIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8ZyBpZD0iSXNvbWV0cmljcyIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPHBhdGggZD0iTTMxLjk4OTkxMzEsMTYuNTM2MDI2NCBMMzEuOTg5OTEzMSwzMiBMMjcuMjY5NzU3NywzMiBMMjcuMjY5NzU3NywyNy4yOTU2MjcgQzI0LjM3Nzg2NjcsMzAuMTU4ODI4OCAyMC4zOTU5NjU0LDMxLjkyNzYzODYgMTYsMzEuOTI3NjM4NiBDNy4xNjM0NDQsMzEuOTI3NjM4NiAwLDI0Ljc4MDM5MzMgMCwxNS45NjM4MTkzIEMwLDcuMTQ3MjQ1MzYgNy4xNjM0NDQsMCAxNiwwIEMyNC44MzY1NTYsMCAzMiw3LjE0NzI0NTM2IDMyLDE1Ljk2MzgxOTMgQzMyLDE2LjE1NTM2MTYgMzEuOTk2NjE4OSwxNi4zNDYxMTYxIDMxLjk4OTkxMzEsMTYuNTM2MDI2NCBaIE0xNS43NTk4Nzg2LDEyLjgzNjc1MDYgQzE0LjkwMzEwMzUsMTIuODM2NzUwNiAxNC4xNjQ4Mjk3LDEzLjE0NTk0MjYgMTMuNTQ1MDM0OSwxMy43NjQzMzU4IEMxMi45MjUyNDAxLDE0LjM4MjcyOSAxMi42MTUzNDc0LDE1LjEyODQyNzMgMTIuNjE1MzQ3NCwxNi4wMDE0NTMgQzEyLjYxNTM0NzQsMTYuODU2MjkwNyAxMi45MjUyNDAxLDE3LjU5Mjg5NTIgMTMuNTQ1MDM0OSwxOC4yMTEyODg0IEMxNC4xNjQ4Mjk3LDE4LjgyOTY4MTYgMTQuOTAzMTAzNSwxOS4xMzg4NzM2IDE1Ljc1OTg3ODYsMTkuMTM4ODczNiBDMTYuNjM0ODgzLDE5LjEzODg3MzYgMTcuMzgyMjcxNCwxOC44Mjk2ODE2IDE4LjAwMjA2NjEsMTguMjExMjg4NCBDMTguNjIxODYwOSwxNy41OTI4OTUyIDE4LjkzMTc1MzYsMTYuODU2MjkwNyAxOC45MzE3NTM2LDE2LjAwMTQ1MyBDMTguOTMxNzUzNiwxNS4xMjg0MjczIDE4LjYyMTg2MDksMTQuMzgyNzI5IDE4LjAwMjA2NjEsMTMuNzY0MzM1OCBDMTcuMzgyMjcxNCwxMy4xNDU5NDI2IDE2LjYzNDg4MywxMi44MzY3NTA2IDE1Ljc1OTg3ODYsMTIuODM2NzUwNiBaIiBpZD0iaW5nYWdlLWNvcHktMTEiIGZpbGw9IiM5RUEzQTgiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE2LjAwMDAwMCwgMTYuMDAwMDAwKSBzY2FsZSgtMSwgMSkgcm90YXRlKDkwLjAwMDAwMCkgdHJhbnNsYXRlKC0xNi4wMDAwMDAsIC0xNi4wMDAwMDApICI+PC9wYXRoPgogICAgPC9nPgo8L3N2Zz4=';

            $page = add_menu_page(
                'Quiero',
                'Quiero',
                'manage_options',
                'qro-pixel-placing-page',
                array(&$this, 'qro_options_panel'),
                $svg_icon
            );
            //     add_options_page('Quiero - Pro', 'Upgrade to Pro', 'manage_options', 'qro-pro', array(&$this, 'qro_pro_sub'));
            add_submenu_page('qro-pixel-placing-page', 'Quiero - Settings', 'Settings', 'manage_options', 'qro-pixel-placing-page', array(&$this, 'qro_options_panel'));

            add_submenu_page('qro-unreachable', 'Quiero - Thank you', 'Thank you', 'manage_options', 'qro-pro-page-thanks', array(&$this, 'qro_thanks'));
            add_submenu_page('qro-pixel-placing-page', 'Quiero - Pro', 'Upgrade to Pro', 'manage_options', 'qro-pro-page', array(&$this, 'qro_pro_sub'));
        }

        function qro_thanks()
        {
?>
            <h2  class="biggerH">
                Quiero - Making Sense of Engagement 
            </h1>
            <hr>
            <h2 class="purple_label">
                ¡Muchas Gracias!
            </h2><br>
            <div class="qro_row">
                <div class="qro_col_1">

                    <div>
                        Thanks for upgrading to a paid plan!
                    </div>
                    <br>
                   <img src="https://media.giphy.com/media/TtUk9GXW50iVq/source.gif"/>
                    <!-- <iframe src="https://giphy.com/embed/TtUk9GXW50iVq" width="480" height="270" frameBorder="0" class="giphy-embed"></iframe> -->
                </div>
                <!-- <div class="qro_col_2">
                    <div id="extraDataBox" class="package">
                        <div class="package_top">
                            <h3 class="package_title" style="text-align:center;">$19 / month</h3>
                        </div>
                        <p style="text-align:center;">Cancel any time, no strings attached</p>
                    </div>
                </div> -->
            </div>
        <?php
            update_option('pro_code', $_GET["qro_code"]);
        }


        function qro_pro_sub()
        {

        ?>
            <!------------------------- QUIERO PRO -------------------------->
            <h2  class="biggerH">
                Quiero - Power up you audiences 
            </h1>
            <hr />
            <?php

            // echo 'is_pro: '. get_option('pro_code', '');
            if (get_option('pro_code', '') != '') { //} && get_option('pro_code', '') == 'checked') {
                // update_option('pro_code', '');
                /** SIM **/
            ?>
                <div class="qro_row">
                    <div class="qro_col_1">
                        <div id="already_paid">
                            <h2 class="purple_label">
                                You are already on Quiero Pro
                            </h2><br>
                            If you need anything, please contact us at <a href="mailto:team@quiero.io">team@quiero.io</a>
                        </div>
                    </div>
                    <div class="qro_col_2">
                        <div id="extraDataBox" class="package">
                            <div class="package_top">
                                <h3 class="package_title" style="text-align:center;">$19 / month</h3>
                            </div>
                            <p style="text-align:center;">Cancel any time, no strings attached</p>
                        </div>
                    </div>
                </div>
            <?php
            } else {
            ?>

                <!-- STRIPE -->
                <script src="https://js.stripe.com/v3/"></script>
                <script>
                    // Create a Stripe client.
                    var stripe = Stripe('pk_live_U9g9GSHMXtIcJyIZtTX1WytX');

                    // Create an instance of Elements.
                    var elements = stripe.elements();

                    // Custom styling can be passed to options when creating an Element.
                    // (Note that this demo uses a wider set of styles than the guide below.)
                    var style = {
                        base: {
                            color: '#32325d',
                            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                            fontSmoothing: 'antialiased',
                            fontSize: '16px',
                            '::placeholder': {
                                color: '#aab7c4'
                            }
                        },
                        invalid: {
                            color: '#fa755a',
                            iconColor: '#fa755a'
                        }
                    };

                    // Create an instance of the card Element.
                    var card = elements.create('card', {
                        style: style
                    });

                    window.addEventListener("DOMContentLoaded", function() {
                        // Add an instance of the card Element into the `card-element` <div>.
                        card.mount('#card-element');


                        // Handle real-time validation errors from the card Element.
                        card.addEventListener('change', function(event) {
                            var displayError = document.getElementById('card-errors');
                            if (event.error) {
                                displayError.textContent = event.error.message;
                            } else {
                                displayError.textContent = '';
                            }
                        });

                        // Handle form submission.
                        var form = document.getElementById('payment-form');
                        form.addEventListener('submit', function(event) {
                            event.preventDefault();

                            stripe.createToken(card).then(function(result) {
                                if (result.error) {
                                    // Inform the user if there was an error.
                                    var errorElement = document.getElementById('card-errors');
                                    errorElement.textContent = result.error.message;
                                } else {
                                    // Send the token to your server.
                                    window.stripeTokenHandler(result.token);
                                }
                            });
                            // Submit the form with the token ID.
                        });

                    });

                    function stripeTokenHandler(token) {
                        // debugger;
                        // Insert the token ID into the form so it gets submitted to the server
                        var form = document.getElementById('payment-form');
                        var hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'stripeToken');
                        hiddenInput.setAttribute('value', token.id);
                        form.appendChild(hiddenInput);

                        // Submit the form
                        form.submit();
                    }
                </script>
                <h2 class="purple_label">
                    Upgrade to Quiero Pro
                </h2><br>
                <div class="qro_row">
                    <div class="qro_col_1">
                        <div id='pro_sub'>
                            <!-- class="pro_area pro_disable"> -->
                            <form method="post" id="payment-form" action="https://hook.integromat.com/b7yg0liltqnl1vebmuxtcq2gjwuk3b4e?source=WP&redirect=
                        <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '-thanks' ?>">
                                <div class="form-row">
                                    <input type="text" id="name" name="name" placeholder="Name" class="stripeLike"><br><br>
                                </div>
                                <div class="form-row">
                                    <input type="text" id="email" name="email" placeholder="E-mail" class="stripeLike"><br><br>
                                </div>
                                <input type="hidden" id="domain" name="domain" value='<?php echo $_SERVER['HTTP_HOST'] ?>'>
                                <input type="hidden" id="full_domain" name="full_domain" value='<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>'>
                                <div class="form-row">
                                    <input type="text" id="coupon" name="coupon" placeholder="Coupon (leave blank if none)" class="stripeLike"><br><br>
                                </div>
                                <div class="form-row">
                                    <div id="card-element">
                                    </div>
                                    <div id="card-errors" role="alert"></div>
                                </div>
                                <br>
                                <!-- <button>Submit Payment</button> -->
                                <input id="submit_payment" class="button qroBtn" type="submit" name="submit_payment" value="Submit Payment" />
                            </form>
                        </div>

                        <div id="testimonial" class="package">
                            <img width="100%" src="<?php echo plugins_url('testimonial.png', __FILE__) ?>">
                        </div>
                    </div>
                    <div class="qro_col_2">
                        <div id="extraDataBox" class="package">
                            <div class="package_top">
                                <h3 class="package_title" style="text-align:center;">$19 / month</h3>
                            </div>
                            <p style="text-align:center;">Cancel any time, no strings attached</p>
                        </div>
                    </div>
                </div>
            <?php
            }
        }


        function qro_wp_head()
        {
            $pro = get_option('pro_code');
            $conf = '';
            if (get_option('has_changes', '')) {
                $conf = array(); //"text" => get_option('qro_insert_header_pro', ''));
                $conf_pro = array();
                //AD PLAFORMS
                if ((get_option('cb_fb', '')) != 'checked') {
                    $conf['nofb'] = true;
                    $conf_pro['nofb'] = true;
                }
                if ((get_option('cb_ga', '')) != 'checked') {
                    $conf['noga'] = true;
                    $conf_pro['nofb'] = true;
                }

                // PRO AD PLATFORMS
                if ((get_option('cb_pn', '')) != 'checked' && $pro) {
                    $conf_pro['nopn'] = true;
                }
                if ((get_option('cb_bi', '')) != 'checked' && $pro) {
                    $conf_pro['nobi'] = true;
                }

                //METRICS
                if ((get_option('cb_time', '')) != 'checked') {
                    $conf['notime'] = true;
                }
                if ((get_option('cb_sd', '')) != 'checked') {
                    $conf['nosd'] = true;
                }
                if ((get_option('cb_pv', '')) != 'checked') {
                    $conf['nopv'] = true;
                }
                if ($pro) {
                    $conf_pro['ces_range'] = 40 + 5 * (get_option('ces_range', 0));
                }

                $conf = json_encode($conf);
                $conf_pro = json_encode($conf_pro);
            }
            quiero_pixel_code($conf);
            if ($pro != false) { //is pro
                quiero_pro_pixel_code(get_option('pro_code'), $conf_pro);
            }
            // return $conf;
        }


        function qro_options_panel()
        { ?>

            <script>
                function setChange() {
                    document.getElementById('has_changes').checked = true;
                }

                function enablePro() {
                    let pro_checked = !!document.getElementById('pro_code').value;

                    document.getElementById('ces_range').disabled = !pro_checked;
                    // document.getElementById('ces_range').classList.remove('inactive');
                    let pro_form_elems = document.getElementById('settings').getElementsByClassName('pro_toggle');
                    for (let i = 0; i < pro_form_elems.length; i++) {
                        // debugger;
                        // if(pro_form_elems[i].classList.contains('inactive'))
                        if (pro_checked) {
                            pro_form_elems[i].classList.remove('inactive');
                            pro_form_elems[i].classList.remove('pro_only');
                            pro_form_elems[i].disabled = false;
                        } else {
                            // pro_form_elems[i].tagName == 'LABEL' &&
                            pro_form_elems[i].classList.add('inactive');
                            // pro_form_elems[i].tagName == 'SPAN' &&
                            pro_form_elems[i].classList.add('pro_only');
                            pro_form_elems[i].checked = false;
                            pro_form_elems[i].disabled = true;
                        }
                        // !pro_checked && pro_form_elems[i].classList.add('inactive');

                    }
                    // let pro_form_elems_input = document.getElementById('settings').getElementsByTagName('input');
                    // for(let i=0;i<pro_form_elems_input.length;i++){
                    //     pro_form_elems_input[i].disabled = !pro_checked;
                    //     pro_checked && pro_form_elems_input[i].classList.remove('inactive');
                    //     !pro_checked && pro_form_elems_input[i].classList.add('inactive');

                    // }
                    // let pro_form_elems_label = document.getElementById('settings').getElementsByTagName('label');
                    // for(let i=0;i<pro_form_elems_label.length;i++){
                    //     pro_checked && pro_form_elems_label[i].classList.remove('inactive');
                    //     !pro_checked && pro_form_elems_input[i].classList.add('inactive');

                    // }
                    // if(document.getElementById('cb_pro').checked){
                    //     document.getElementById('pro_sub').classList.remove('pro_disable');
                    //     //remove disable from form / or all elements
                    //     document.getElementById('ces_range').disabled = !pro_checked;                        
                    // }
                }

                //initial set
                window.addEventListener("DOMContentLoaded", function() {
                    enablePro();

                    if (document.getElementById('has_changes').checked == false) {
                        document.getElementById('cb_fb').checked = true;
                        document.getElementById('cb_ga').checked = true;

                        document.getElementById('cb_time').checked = true;
                        document.getElementById('cb_sd').checked = true;
                        document.getElementById('cb_pv').checked = true;
                    }
                }, false);
            </script>

            <div id="fb-root"></div>
            <div id="shfs-wrap">
                <div class="wrap">
                    <h2>Quiero - Making Sense of Engagement </h2>
                    <hr />
                    <div class="qro_row">
                        <div class="qro_col_1">
                            <div class="shfs-wrap" style="width: 100%;float: left;margin-right: 2rem;">

                                <form name="dofollow" action="options.php" method="post">

                                    <h2 class="purple_label">
                                        ¡Hola, Welcome to Quiero!
                                    </h2>
                                    <div id="welcome_text">
                                        With Quiero you can easily create better remarketing and lookalike audiences.<br>
                                        Quiero highlights key user actions (e.g. scroll depth) directly in your pixels (e.g. Google & Facebook) <br>
                                        so you can create better audiences without wasting time and money on coding.</div>
                                    <ol>
                                        <!-- <li><span>¡</span>To run Quiero Basic just click here <span>!</span></li> -->
                                        <div id="settings" class="contentDiv">
                                            <!-- --------------------------- Ad Platforms --------------------------- -->
                                            <span class="settings_group">Ad Platforms
                                                <span class="learn_more">?</span><span class="tooltip">Select the pixels you want Quiero to integrate with</span></span>
                                            <br><br>
                                            <label class="switch">
                                                <label class="checkbox_label">Facebook</label>
                                                <input id="cb_fb" type="checkbox" name="cb_fb" <?php echo get_option('cb_fb', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                            </label>
                                            <br>
                                            <label class="switch">
                                                <label class="checkbox_label">Google</label>
                                                <input id="cb_ga" type="checkbox" name="cb_ga" <?php echo get_option('cb_ga', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                            </label>
                                            <br>

                                            <label class="switch">
                                                <label class="checkbox_label inactive pro_toggle">Pinterest</label>
                                                <input disabled id="cb_pn" type="checkbox" name="cb_pn" class="pro_toggle" <?php echo get_option('cb_pn', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                                <span class="tooltip pro_only inactive pro_toggle"> Available only for Quiero Pro</span>
                                            </label>
                                            <br>
                                            <label class="switch">
                                                <label class="checkbox_label inactive pro_toggle">Bing</label>
                                                <input disabled id="cb_bi" type="checkbox" name="cb_bi" class="pro_toggle" <?php echo get_option('cb_bi', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                                <span class="tooltip pro_only inactive pro_toggle"> Available only for Quiero Pro</span>
                                            </label>
                                            <br>
                                            <br><br>

                                            <!-- --------------------------- Engagement Metrics --------------------------- -->
                                            <span class="settings_group">Engagement Metrics <span class="learn_more">?</span>
                                                <span class="tooltip"> Select which engagement metrics you want to track</span></span>
                                            <br><br>
                                            <label class="switch">
                                                <label class="checkbox_label">Time on Page</label>
                                                <input id="cb_time" type="checkbox" name="cb_time" <?php echo get_option('cb_time', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                            </label> <br>
                                            <label class="switch">
                                                <label class="checkbox_label">Scroll Depth</label>
                                                <input id="cb_sd" type="checkbox" name="cb_sd" <?php echo get_option('cb_sd', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                            </label> <br>
                                            <label class="switch">
                                                <label class="checkbox_label">Page views</label>
                                                <input id="cb_pv" type="checkbox" name="cb_pv" <?php echo get_option('cb_pv', '') ?> value='checked' onchange="setChange()">
                                                <span class="cb_slider round"></span>
                                            </label>
                                            <br><br>
                                            <br>
                                            <!-- --------------------------- Combined Engagement Score --------------------------- -->

                                            <span class="settings_group inactive pro_toggle">Combined Engagement Score <span class="learn_more">?</span> <span class="tooltip"> A unified engagement signal crafted by our Data Science team</span> </span>
                                            <br>
                                            <div id="additional_info">
                                                Adjust the dial to target your optimal audience
                                            </div>
                                            <br><br>
                                            <div class="value_slide_container">
                                                <label class="purple_label">Scale</label>
                                                <input type="range" min="-2" max="2" value="<?php echo get_option('ces_range', '0') ?>" class="value_slider inactive pro_toggle" id="ces_range" name="ces_range" disabled>
                                                <label class="purple_label">Accuracy</label>
                                                <span class="tooltip pro_only inactive pro_toggle"> Available only for Quiero Pro</span>
                                            </div>
                                            <!-- <br><br> -->
                                        </div>
                                        <?php settings_fields('insert-quiero-pixel'); ?>
                                        <!-- <input id="insert_header" name="qro_insert_header_pro" value="< ?php echo esc_html(get_option('qro_insert_header_pro')); ?>"> -->
                                        <input id="has_changes" type="checkbox" name="has_changes" <?php echo get_option('has_changes', '') ?> value='checked' style='display:none' />
                                        <input type="hidden" id="pro_code" name="pro_code" value="<?php echo get_option('pro_code', '') ?>" />
                                        <li class="submit">
                                            <input id="letsGoBtn" class="button qroBtn" type="submit" name="save" value="Save" />
                                        </li>

                                        <!-- <li class="submit">
                                </li> -->
                                    </ol>
                                </form>
                                <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?') . '?page=qro-pro-page'; ?>">
                                    <?php if (get_option('pro_code', '') == '') { //} && get_option('pro_code', '') == 'checked') {
                                    ?>
                                        <img width="100%" src="https://static.quiero.io/assets/QP_upgrade.png"/>
                                    <?php } ?>
                                </a>

                            </div>
                        </div>
                        <div class="qro_col_2">
                            <div id="extraDataBox" class="package">
                                <div class="package_top">
                                    <h3 class="package_title">Getting Started with Quiero</h3>
                                    <ul>
                                        <li><a target="_blank" href="https://quiero.io/combined-engagement-score"> The Quiero Combined Engagement Score</a></li>
                                        <li><a target="_blank" href="https://quiero.io/configuring-quiero/"> Setting up Quiero</a></li>
                                    </ul>
                                    <hr>
                                    <h3 class="package_title">Using Quiero on Facebook Ads</h3>
                                    <ul>
                                        <li> <a target="_blank" href="https://quiero.io/creating-quiero-audiences-in-facebook-ads/"> Creating Quiero Audiences in Facebook Ads</a></li>
                                        <li> <a target="_blank" href="https://quiero.io/checking-the-quiero-events-on-your-facebook-pixel"> Checking the Quiero events on your Facebook Pixel</a></li>
                                    </ul>
                                </div>
                                <hr>
                                <div class="package_top">
                                    <h3 class="package_title">Using Quiero on Google Ads</h3>
                                    <ul>
                                        <li> <a target="_blank" href="https://quiero.io/creating-quiero-audiences-in-google-ads"> Creating Quiero Audiences in Google Ads </a></li>
                                        <li> <a target="_blank" href="https://quiero.io/checking-the-quiero-events-in-google-analytics"> Checking the Quiero events in Google Analytics</a></li>
                                    </ul>
                                </div>
                                <hr>
                                <p style="text-align:center;"><a target="_blank" href="https://quiero.io/resources">Visit our Knowledge Base</a></p>
                            </div>
                        </div>
                    </div>
        <?php
        }
    }
    function quiero_pixel_code($QRO_CONF = '')
    {
        $script_src = 'https://static.quiero.io/quiero-basic.js?src=WP';
        wp_enqueue_script('qro_pxl_script', $script_src);
        wp_add_inline_script('qro_pxl_script', 'window.runQRO && runQRO(' . $QRO_CONF . ');');
    }
    function quiero_pro_pixel_code($QRO_CODE, $QRO_CONF = '')
    {
        $script_src = 'https://static.quiero.io/quiero-pro.js?src=WP';
        wp_enqueue_script('qro_pro_pxl_script', $script_src);
        wp_add_inline_script('qro_pro_pxl_script', 'window.runQRPRO && runQRPRO("' . $QRO_CODE . '",' . $QRO_CONF . ');');
    }

    $qro_pxl_scripts = new QuieroPixelScript();
}
