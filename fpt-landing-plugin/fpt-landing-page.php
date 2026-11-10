<?php
/**
 * Plugin Name: FPT Landing Page Đăng Ký
 * Description: Landing page dịch vụ Internet FPT responsive, quản lý gói cước và lưu khách hàng đăng ký. Dùng shortcode [fpt_landing_page].
 * Version: 1.9.24
 * Author: OpenAI
 * Text Domain: fpt-landing-page
 */
if (!defined('ABSPATH')) exit;
require_once __DIR__.'/includes/content.php';
require_once __DIR__.'/includes/registration.php';
require_once __DIR__.'/includes/assets.php';

class FPT_Landing_Page {
    use FPTLP_Content;
    use FPTLP_Registration;
    use FPTLP_Assets;
    const OPT = 'fptlp_settings';
    private $admin_page_hooks = [];
    public function __construct() {
        add_action('init', [$this, 'register_campaign_routes']);
        add_action('admin_init', [$this, 'refresh_campaign_routes']);
        add_filter('query_vars', [$this, 'campaign_query_vars']);
        add_filter('redirect_canonical', [$this, 'campaign_redirect'], 10, 2);
        register_deactivation_hook(__FILE__, [$this, 'deactivate_campaign_routes']);
        add_action('init', [$this, 'register_leads']);
        add_action('init', [$this, 'schedule_guard_cleanup']);
        add_action('fptlp_guard_cleanup', [$this, 'cleanup_guards']);
        add_action('admin_post_fptlp_resend', [$this, 'resend_notification']);
        add_action('add_meta_boxes', [$this, 'register_mail_box']);
        add_action('admin_footer', [$this, 'resend_form']);
        add_action('admin_notices', [$this, 'mail_notice']);
        add_action('manage_fptlp_lead_posts_custom_column', [$this, 'mail_column'], 10, 2);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'migrate_package_tabs_once']);
        add_shortcode('fpt_landing_page', [$this, 'shortcode']);
        add_action('admin_post_nopriv_fptlp_submit', [$this, 'submit']);
        add_action('admin_post_fptlp_submit', [$this, 'submit']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }
    private function campaign_rules() {
        $rules=[];
        foreach (['internet','combo-tv','combo-vvip','gaming'] as $group) {
            // A real child page always takes precedence over a virtual campaign URL.
            if (get_page_by_path('landing-fpt/'.$group)) continue;
            $rules['^landing-fpt/'.$group.'/?$']='index.php?pagename=landing-fpt&fptlp_route_group='.$group;
        }
        return $rules;
    }
    public function register_campaign_routes() {
        foreach ($this->campaign_rules() as $pattern=>$query) add_rewrite_rule($pattern,$query,'top');
    }
    public function campaign_query_vars($vars) { $vars[]='fptlp_route_group'; return $vars; }
    private function campaign_group() {
        $group=get_query_var('fptlp_route_group','');
        return is_page('landing-fpt') && is_string($group) && in_array($group,['internet','combo-tv','combo-vvip','gaming'],true) ? $group : '';
    }
    public function campaign_redirect($redirect,$requested) {
        // Keep the campaign path instead of WordPress redirecting to the parent page.
        return $this->campaign_group()!=='' ? false : $redirect;
    }
    public function refresh_campaign_routes() {
        if (!current_user_can('manage_options')) return;
        $signature=md5(wp_json_encode($this->campaign_rules()));
        if (get_option('fptlp_campaign_routes_v1')===$signature) return;
        flush_rewrite_rules(false);
        update_option('fptlp_campaign_routes_v1',$signature);
    }
    public function deactivate_campaign_routes() {
        global $wp_rewrite;
        foreach (['internet','combo-tv','combo-vvip','gaming'] as $group) unset($wp_rewrite->extra_rules_top['^landing-fpt/'.$group.'/?$']);
        delete_option('fptlp_campaign_routes_v1');
        flush_rewrite_rules(false);
    }
    public function defaults() {
        return [
            'landing_content' => $this->content_defaults(),
            'vvip_label'=>'COMBO NỔI BẬT', 'vvip_title'=>'Combo Wi-Fi – Truyền hình V.VIP',
            'vvip_description'=>'Các lựa chọn kết hợp Internet và dịch vụ truyền hình V.VIP.',
            'vvip_background'=>'#fff8f1','vvip_background_end'=>'#ffffff','vvip_border'=>'#ffd5b5',
            'vvip_label_color'=>'#b84b06','vvip_title_color'=>'#a64208','vvip_description_color'=>'#667085',
            'vvip_show_notice'=>'1',
            'wpforms_shortcode'=>'', 'wpforms_heading'=>'Đăng ký tư vấn lắp mạng', 'wpforms_intro'=>'Để lại thông tin để được tư vấn gói phù hợp và báo giá tại khu vực của bạn.',
            'service_area'=>'', 'hero_note'=>'Giá và ưu đãi được xác nhận theo địa chỉ lắp đặt.', 'hero_cta'=>'Nhận báo giá tại địa chỉ của bạn', 'sync_price_table'=>'1', 'ads_send_to'=>'',
            'hide_empty_categories' => '0',
            'trust_1'=>'Tư vấn theo nhu cầu', 'trust_2'=>'Hỗ trợ chọn gói', 'trust_3'=>'Xác nhận khu vực lắp đặt',
            'brand' => 'FPT Internet', 'eyebrow' => 'KẾT NỐI NHANH – TRẢI NGHIỆM MƯỢT',
            'headline' => 'Lắp Internet FPT cho gia đình',
            'intro' => 'Tư vấn gói Internet phù hợp nhu cầu sử dụng. Để lại thông tin, nhân viên sẽ liên hệ xác nhận giá và ưu đãi theo khu vực.',
            'phone' => '0906711467', 'cta' => 'Đăng ký tư vấn', 'accent' => '#f57e20', 'banner_url' => '', 'banner_slide_2'=>'', 'banner_slide_3'=>'', 'banner_slide_4'=>'', 'banner_slide_5'=>'', 'banner_slide_6'=>'',
            'notice' => 'Giá và ưu đãi có thể thay đổi theo khu vực, thời điểm.', 'package_default_category' => 'combo-vvip', 'notification_email' => get_option('admin_email'), 'registration_heading' => 'Đăng ký wifi FPT',
            'packages' => "Internet Giga|195.000đ|300 Mbps / 300 Mbps|Modem Wi-Fi 6;Kết nối ổn định;Phù hợp gia đình|Giá tốt\nInternet Sky|220.000đ|1 Gbps / 300 Mbps|Modem Wi-Fi 6;Xem phim mượt;Hỗ trợ tư vấn lắp đặt|Phổ biến\nInternet Meta|300.000đ|1 Gbps / 1 Gbps|Modem Wi-Fi 6;Phù hợp nhiều thiết bị;Tốc độ đối xứng|Tốc độ cao\nCombo Giga V.VIP|220.000đ|300 Mbps / 300 Mbps|Modem Wi-Fi 6;Xem Ngoại hạng Anh;Ưu đãi tùy khu vực|Ưu đãi\nCombo Sky V.VIP|239.000đ|1 Gbps / 300 Mbps|Modem Wi-Fi 6;Truyền hình FPT Play;Tư vấn lắp đặt|Bán chạy\nCombo Meta V.VIP|339.000đ|1 Gbps / 1 Gbps|Modem Wi-Fi 6;Internet kết hợp truyền hình;Giải trí đa thiết bị|Cao cấp",
        ];
    }
    private function package_categories() {
        return [
            'internet' => ['label'=>'Internet cá nhân', 'title'=>'Các gói Internet cá nhân', 'description'=>'Internet Wi-Fi cho gia đình và nhu cầu sử dụng hằng ngày.'],
            'combo-tv' => ['label'=>'Combo truyền hình FPT', 'title'=>'Combo Internet – Truyền hình FPT', 'description'=>'Kết hợp Internet với nội dung giải trí FPT Play.'],
            'combo-vvip' => ['label'=>'Combo truyền hình V.VIP', 'title'=>'Ưu đãi Combo Wi-Fi – Truyền hình FPT V.VIP', 'description'=>'Các lựa chọn kết hợp Internet và dịch vụ truyền hình V.VIP.'],
            'gaming' => ['label'=>'Internet Game thủ', 'title'=>'Các gói Internet dành cho game thủ', 'description'=>'Tư vấn đường truyền phù hợp nhu cầu chơi game và livestream.'],
            'business' => ['label'=>'Internet Doanh nghiệp', 'title'=>'Giải pháp Internet cho doanh nghiệp', 'description'=>'Tư vấn kết nối theo quy mô và nhu cầu vận hành.'],
        ];
    }
    private function package_category_from_name($name) {
        $name = strtolower(remove_accents($name));
        if (strpos($name,'doanh nghiep') !== false || strpos($name,'business') !== false || strpos($name,'company') !== false) return 'business';
        if (strpos($name,'game') !== false || strpos($name,'esport') !== false) return 'gaming';
        if (strpos($name,'v.vip') !== false || strpos($name,'v vip') !== false || strpos($name,'vvip') !== false) return 'combo-vvip';
        if (strpos($name,'combo') !== false || strpos($name,'truyen hinh') !== false || strpos($name,'fpt play') !== false) return 'combo-tv';
        return 'internet';
    }
    /** Creates a separate editable list for each tab without changing previous package records. */
    private function initial_package_groups($raw) {
        $groups = array_fill_keys(array_keys($this->package_categories()), '');
        $lines_by_group = array_fill_keys(array_keys($this->package_categories()), []);
        foreach (preg_split('/\r\n|\r|\n/', (string)$raw) as $line) {
            if (!trim($line)) continue;
            $pieces=explode('|', $line);
            $name=trim($pieces[0] ?? '');
            if (!$name) continue;
            $explicit=trim($pieces[7] ?? '');
            $category=array_key_exists($explicit, $groups) ? $explicit : $this->package_category_from_name($name);
            $lines_by_group[$category][]=$line;
        }
        // Three additional editable examples from the user-supplied V.VIP reference.
        // These are reference data; the store owner must verify prices and specifications.
        $vvip_examples=[
            'Combo SpeedX1 V.VIP|369.000đ|1 Gbps / 1 Gbps|HUB Wi-Fi 7;Tư vấn quyền lợi truyền hình;Ưu đãi tùy khu vực|Tham khảo',
            'Combo SpeedX2 V.VIP|1.084.000đ|2 Gbps / 2 Gbps|Thiết bị Wi-Fi 7;Tư vấn dịch vụ giải trí;Ưu đãi tùy khu vực|Tham khảo',
            'Combo SpeedX10 V.VIP|1.684.000đ|10 Gbps / 10 Gbps|Giải pháp Internet tốc độ cao;Tư vấn thiết bị phù hợp;Giá tùy khu vực|Tham khảo',
        ];
        $seen=[];
        foreach ($lines_by_group['combo-vvip'] as $line) {
            $parts=explode('|',$line);
            $seen[strtolower(remove_accents(trim($parts[0] ?? '')))] = true;
        }
        foreach ($vvip_examples as $line) {
            $parts=explode('|',$line);
            $name=strtolower(remove_accents(trim($parts[0] ?? '')));
            if (count($lines_by_group['combo-vvip']) < 6 && !isset($seen[$name])) {
                $lines_by_group['combo-vvip'][]=$line;
                $seen[$name]=true;
            }
        }
        foreach ($lines_by_group as $key=>$lines) $groups[$key]=implode("\n",$lines);
        return $groups;
    }
    private function current_package_groups($settings) {
        if (isset($settings['packages_by_category']) && is_array($settings['packages_by_category'])) {
            return array_merge(array_fill_keys(array_keys($this->package_categories()), ''), array_intersect_key($settings['packages_by_category'], $this->package_categories()));
        }
        return $this->initial_package_groups($settings['packages'] ?? '');
    }
    public function migrate_package_tabs_once() {
        if (!current_user_can('manage_options') || get_option('fptlp_tabs_170_migrated')) return;
        $stored=get_option(self::OPT, []);
        if (!is_array($stored)) $stored=[];
        if (!isset($stored['packages_by_category']) || !is_array($stored['packages_by_category'])) {
            $stored['packages_by_category']=$this->initial_package_groups($stored['packages'] ?? $this->defaults()['packages']);
            $stored['package_default_category']='combo-vvip';
            update_option(self::OPT,$stored);
        }
        update_option('fptlp_tabs_170_migrated',1);
    }
    public function settings() { return wp_parse_args(get_option(self::OPT, []), $this->defaults()); }
    public function register_leads() {
        register_post_type('fptlp_lead', [
            'labels' => ['name' => 'Khách đăng ký FPT', 'singular_name' => 'Khách đăng ký', 'menu_name' => 'Khách đăng ký'],
            'public' => false, 'show_ui' => true, 'show_in_menu' => 'fptlp', 'supports' => ['title'],
            'capability_type' => 'post', 'map_meta_cap' => false,
            // The leads screen contains customer details; use the same permission as plugin settings.
            'capabilities' => array_fill_keys(['edit_post','read_post','delete_post','edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','delete_private_posts','delete_published_posts','delete_others_posts','edit_private_posts','edit_published_posts','create_posts','read'], 'manage_options'),
            'menu_icon' => 'dashicons-phone',
        ]);
    }
    public function admin_menu() {
        $this->admin_page_hooks[] = add_menu_page('FPT Landing Page', 'FPT Landing Page', 'manage_options', 'fptlp', [$this, 'settings_page'], 'dashicons-layout', 58);
        $this->admin_page_hooks[] = add_submenu_page('fptlp', 'Cài đặt chung', 'Cài đặt chung', 'manage_options', 'fptlp', [$this, 'settings_page']);
        $this->admin_page_hooks[] = add_submenu_page('fptlp', 'Quản lý gói cước', 'Quản lý gói cước', 'manage_options', 'fptlp-packages', [$this, 'packages_page']);
        $this->content_menus();
        $this->admin_page_hooks[] = add_submenu_page('fptlp', 'Quản lý banner', 'Quản lý banner', 'manage_options', 'fptlp-banners', [$this, 'banner_page']);
        $this->admin_page_hooks[] = add_submenu_page('fptlp', 'Cài đặt đăng ký', 'Cài đặt đăng ký', 'manage_options', 'fptlp-registration', [$this, 'registration_page']);
    }
    public function register_settings() { register_setting('fptlp_group', self::OPT, [$this, 'sanitize_settings']); }
    public function sanitize_settings($input) {
        $out = $this->settings();
        if (!is_array($input)) return $out;
        // A final marker detects PHP max_input_vars truncation before any group is replaced.
        if (isset($input['package_groups']) && empty($input['package_editor_complete'])) {
            unset($input['package_groups']);
            add_settings_error(self::OPT, 'incomplete-packages', 'Chưa lưu gói cước vì biểu mẫu gửi lên chưa đầy đủ. Hãy tăng max_input_vars trên hosting rồi lưu lại.', 'error');
        }
        foreach (['brand','eyebrow','headline','intro','phone','cta','notice','registration_heading','wpforms_heading','wpforms_intro','service_area','hero_note','hero_cta'] as $key) $out[$key] = isset($input[$key]) ? sanitize_text_field($input[$key]) : $out[$key];
        if (isset($input['wpforms_shortcode'])) {
            $code = is_string($input['wpforms_shortcode']) ? trim($input['wpforms_shortcode']) : '';
            if ($code === '' || preg_match('/^\[wpforms\s+[^\[\]<>]*\]$/D', $code)) {
                $atts = $code === '' ? [] : shortcode_parse_atts(substr($code, 8, -1));
                if ($code === '' || (is_array($atts) && !empty($atts['id']) && ctype_digit((string)$atts['id']) && (int)$atts['id'] > 0)) {
                    $out['wpforms_shortcode'] = $code;
                } else {
                    add_settings_error(self::OPT, 'wpforms-id', 'Shortcode WPForms cần ID form hợp lệ.', 'error');
                }
            } else {
                add_settings_error(self::OPT, 'wpforms-code', 'Hãy nhập shortcode dạng [wpforms id="123"] hoặc để trống để ẩn form.', 'error');
            }
        }
        $out['intro'] = isset($input['intro']) ? sanitize_textarea_field($input['intro']) : $out['intro'];
        foreach (range(2,6) as $n) { $field='banner_slide_'.$n; if (isset($input[$field]) && is_string($input[$field])) $out[$field]=esc_url_raw($input[$field]); }
        $out['banner_url'] = isset($input['banner_url']) ? esc_url_raw($input['banner_url']) : $out['banner_url'];
        $out['accent'] = isset($input['accent']) && preg_match('/^#[0-9a-fA-F]{6}$/', $input['accent']) ? $input['accent'] : $out['accent'];
        $out['packages'] = isset($input['packages']) ? sanitize_textarea_field($input['packages']) : $out['packages'];
        // WordPress may sanitize twice when an option is created. Accept the normalized
        // output as well as old text records so saving remains idempotent.
        if (isset($input['packages_by_category']) && is_array($input['packages_by_category'])) {
            $groups = $this->current_package_groups($out);
            foreach ($this->package_categories() as $key => $info) {
                if (!isset($input['packages_by_category'][$key])) continue;
                $value = $input['packages_by_category'][$key];
                if (is_string($value)) $groups[$key] = sanitize_textarea_field($value);
                elseif (is_array($value) && !isset($input['package_groups'][$key])) {
                    $input['package_groups'][$key] = ['present'=>1, 'items'=>$value];
                }
            }
            $out['packages_by_category'] = $groups;
        }
        // Structured fields coexist with the legacy text format until each group is saved.
        if (isset($input['package_groups']) && is_array($input['package_groups'])) {
            $groups = $this->current_package_groups($out);
            foreach ($this->package_categories() as $key => $info) {
                if (!isset($input['package_groups'][$key]['present'])) continue;
                $rows = $input['package_groups'][$key]['items'] ?? [];
                if (!is_array($rows)) continue;
                $clean = []; $valid = true;
                foreach ($rows as $row) {
                    if (!is_array($row)) { $valid = false; break; }
                    $item = [];
                    foreach (['name','price','download','upload','badge','suitable_for'] as $field) {
                        $item[$field] = isset($row[$field]) && is_scalar($row[$field]) ? sanitize_text_field($row[$field]) : '';
                    }
                    if ($item['name'] === '') { $valid = false; break; }
                    foreach (['image','detail'] as $field) {
                        $item[$field] = isset($row[$field]) && is_scalar($row[$field]) ? esc_url_raw($row[$field]) : '';
                    }
                    $item['meter_level'] = $this->meter_level($row);
                    $item['benefits'] = [];
                    foreach (is_array($row['benefits'] ?? null) ? $row['benefits'] : [] as $benefit) {
                        if (!is_scalar($benefit)) continue;
                        $benefit = sanitize_text_field($benefit);
                        if ($benefit !== '') $item['benefits'][] = $benefit;
                    }
                    $item['equipment'] = [];
                    foreach (is_array($row['equipment'] ?? null) ? $row['equipment'] : [] as $device) {
                        if (!is_array($device)) { $valid = false; break; }
                        $title = isset($device['title']) && is_scalar($device['title']) ? sanitize_text_field($device['title']) : '';
                        $image = isset($device['image']) && is_scalar($device['image']) ? esc_url_raw($device['image']) : '';
                        if ($title === '' && $image === '') continue;
                        if ($title === '') { $valid = false; break; }
                        $item['equipment'][] = ['title'=>$title, 'image'=>$image];
                    }
                    $item['region_prices'] = []; $seen_regions = [];
                    foreach (is_array($row['region_prices'] ?? null) ? $row['region_prices'] : [] as $region) {
                        if (!is_array($region)) { $valid = false; break; }
                        $label = isset($region['label']) && is_scalar($region['label']) ? sanitize_text_field($region['label']) : '';
                        $price = isset($region['price']) && is_scalar($region['price']) ? sanitize_text_field($region['price']) : '';
                        if ($label === '' && $price === '') continue;
                        $region_slug = sanitize_title($label);
                        if ($region_slug === '' || $price === '' || isset($seen_regions[$region_slug])) { $valid = false; break; }
                        $seen_regions[$region_slug] = true;
                        $item['region_prices'][] = ['label'=>$label, 'price'=>$price];
                    }
                    $clean[] = $item;
                }
                if ($valid) $groups[$key] = $clean;
                else add_settings_error(self::OPT, 'package-name-'.$key, 'Chưa lưu nhóm '.$info['label'].': hãy kiểm tra tên gói, tên thiết bị và giá khu vực (không để thiếu hoặc trùng khu vực).', 'error');
            }
            $out['packages_by_category'] = $groups;
        }
        if (isset($input['package_default_category'])) {
            $chosen=sanitize_key($input['package_default_category']);
            if (isset($this->package_categories()[$chosen]) && $chosen !== 'business') $out['package_default_category']=$chosen;
        }

        foreach (['trust_1','trust_2','trust_3'] as $field) if (isset($input[$field]) && is_scalar($input[$field])) $out[$field]=sanitize_text_field($input[$field]);
        if (isset($input['hide_empty_categories'])) $out['hide_empty_categories'] = $input['hide_empty_categories']==='1' ? '1' : '0';
        if (isset($input['notification_email'])) $out['notification_email'] = sanitize_email($input['notification_email']);
        if (isset($input['sync_price_table'])) $out['sync_price_table']=$input['sync_price_table']==='1'?'1':'0';
        if (isset($input['ads_send_to']) && is_string($input['ads_send_to'])) {
            $value=trim($input['ads_send_to']);
            if ($value==='' || preg_match('~^AW-[0-9]+/[A-Za-z0-9_-]+$~D',$value)) $out['ads_send_to']=$value;
            else add_settings_error(self::OPT,'invalid-ads','Mã chuyển đổi phải có dạng AW-123456789/nhãn_chuyển_đổi.','error');
        }
        foreach (['vvip_label','vvip_title','vvip_description'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) $out[$key]=sanitize_text_field($input[$key]);
        }
        foreach (['vvip_background','vvip_background_end','vvip_border','vvip_label_color','vvip_title_color','vvip_description_color'] as $key) {
            if (isset($input[$key]) && is_string($input[$key]) && preg_match('/^#[0-9a-fA-F]{6}$/D',$input[$key])) $out[$key]=$input[$key];
        }
        if (isset($input['vvip_show_notice'])) $out['vvip_show_notice']=$input['vvip_show_notice']==='1'?'1':'0';
        return $this->sanitize_content($input, $out);
    }
    public function settings_page() {
        if (!current_user_can('manage_options')) return; $s = $this->settings(); ?>
        <div class="wrap fptlp-admin"><h1>FPT Landing Page – Cài đặt</h1><?php settings_errors(); ?>
        <p>Dùng shortcode <code>[fpt_landing_page]</code> trong trang WordPress hoặc widget Shortcode của Elementor Free.</p>
        <form method="post" action="options.php"><?php settings_fields('fptlp_group'); ?>
        <table class="form-table" role="presentation">
        <?php $fields = ['brand'=>'Tên thương hiệu','eyebrow'=>'Dòng nhấn nhỏ','headline'=>'Tiêu đề chính','intro'=>'Mô tả','service_area'=>'Khu vực phục vụ (chỉ nhập khu vực đã xác nhận)','hero_note'=>'Giá / điều kiện hiển thị đầu trang','hero_cta'=>'Nút báo giá đầu trang','phone'=>'Số điện thoại hotline','cta'=>'Nội dung nút đăng ký','notice'=>'Ghi chú giá/ưu đãi'];
        foreach ($fields as $key=>$label) { echo '<tr><th><label for="fptlp_'.$key.'">'.esc_html($label).'</label></th><td>'; if (in_array($key,['intro','notice'])) echo '<textarea class="large-text" rows="3" id="fptlp_'.$key.'" name="'.self::OPT.'['.$key.']">'.esc_textarea($s[$key]).'</textarea>'; else echo '<input class="regular-text" id="fptlp_'.$key.'" name="'.self::OPT.'['.$key.']" value="'.esc_attr($s[$key]).'">'; echo '</td></tr>'; }
        ?>
        <tr><th colspan="2"><h2>Form WPForms phía trên khu vực lắp đặt</h2><p class="description">Tạo form trong WPForms, sao chép shortcode rồi dán bên dưới. Đổi ID để thay form. Để trống để ẩn. Trường nhập, thông báo thành công và email nhận được cấu hình trong WPForms.</p></th></tr>
        <?php foreach (['wpforms_shortcode'=>'Shortcode WPForms (ví dụ [wpforms id="123"])','wpforms_heading'=>'Tiêu đề form','wpforms_intro'=>'Mô tả form'] as $field=>$label): ?>
        <tr><th><label for="fptlp_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th><td><input class="large-text" id="fptlp_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr(self::OPT.'['.$field.']'); ?>" value="<?php echo esc_attr($s[$field]); ?>"></td></tr>
        <?php endforeach; ?>
        <tr><th colspan="2"><h2>Khu vực Combo V.VIP</h2><p class="description">Chỉnh phần tiêu đề và nền bao quanh nhóm V.VIP. Màu chữ và nút trên từng card gói cước được quản lý riêng trong giao diện hiện tại.</p></th></tr>
        <?php foreach (['vvip_label'=>'Nhãn nhỏ phía trên','vvip_title'=>'Tiêu đề chính','vvip_description'=>'Mô tả dưới tiêu đề'] as $field=>$label): ?>
        <tr><th><label for="fptlp_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th><td><input class="large-text" id="fptlp_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr(self::OPT.'['.$field.']'); ?>" value="<?php echo esc_attr($s[$field]); ?>"><p class="description">Để trống để ẩn dòng này.</p></td></tr>
        <?php endforeach; ?>
        <tr><th>Ghi chú giá chung</th><td><input type="hidden" name="<?php echo esc_attr(self::OPT); ?>[vvip_show_notice]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(self::OPT); ?>[vvip_show_notice]" value="1" <?php if ($s['vvip_show_notice']==='1') echo 'checked'; ?>> Hiển thị thêm Ghi chú giá/ưu đãi sau mô tả V.VIP</label></td></tr>
        <?php foreach (['vvip_background'=>'Màu nền đầu','vvip_background_end'=>'Màu nền cuối','vvip_border'=>'Màu viền và đường phân cách','vvip_label_color'=>'Màu nhãn nhỏ','vvip_title_color'=>'Màu tiêu đề','vvip_description_color'=>'Màu mô tả'] as $field=>$label): ?>
        <tr><th><label for="fptlp_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th><td><input type="color" id="fptlp_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr(self::OPT.'['.$field.']'); ?>" value="<?php echo esc_attr($s[$field]); ?>"> <span class="description">Mặc định: <?php echo esc_html($this->defaults()[$field]); ?></span></td></tr>
        <?php endforeach; ?><tr><td colspan="2"><p class="description">Chọn cùng màu nền đầu và cuối để dùng nền một màu. Bấm Lưu cài đặt, sau đó xóa cache nếu website chưa cập nhật.</p></td></tr>
        <tr><th><label for="fptlp_accent">Màu chủ đạo</label></th><td><input type="color" id="fptlp_accent" name="<?php echo esc_attr(self::OPT); ?>[accent]" value="<?php echo esc_attr($s['accent']); ?>"></td></tr>


        <tr><th>Cam kết hỗ trợ</th><td><?php foreach (['trust_1','trust_2','trust_3'] as $i=>$field): ?><p><label for="fptlp_<?php echo esc_attr($field); ?>">Nội dung <?php echo (int)$i+1; ?></label><br><input class="regular-text" id="fptlp_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr(self::OPT.'['.$field.']'); ?>" value="<?php echo esc_attr($s[$field]); ?>"></p><?php endforeach; ?><p class="description">Ba dòng có dấu ✓ dưới phần giới thiệu đầu trang. Để trống dòng nào để ẩn dòng đó.</p></td></tr>
        <tr><th>Danh mục trống</th><td><input type="hidden" name="<?php echo esc_attr(self::OPT); ?>[hide_empty_categories]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(self::OPT); ?>[hide_empty_categories]" value="1" <?php if ($s['hide_empty_categories']==='1') echo 'checked'; ?>> Ẩn danh mục chưa có gói, bao gồm mục doanh nghiệp</label><p class="description">Bật để trang gọn hơn. Các danh mục vẫn chỉnh sửa được trong quản trị.</p></td></tr>
        <tr><th>Danh sách gói cước</th><td><p>V.VIP hiển thị riêng phía trên, ba nhóm còn lại dùng chung thanh tab và doanh nghiệp hiển thị riêng phía dưới; mỗi nhóm hiển thị tối đa 6 gói.</p><p><a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=fptlp-packages')); ?>">Mở Quản lý gói cước theo tab</a></p></td></tr>
        </table><?php submit_button('Lưu cài đặt'); ?></form>
        <hr><h2>Hướng dẫn</h2><p>Khách gửi biểu mẫu sẽ được lưu trong menu <strong>Khách đăng ký</strong>. Hãy giới hạn quyền truy cập quản trị và xử lý dữ liệu khách hàng theo chính sách bảo mật của bạn.</p></div><?php
    }

    private function admin_start($title) {
        if (!current_user_can('manage_options')) return false;
       
        echo '<div class="wrap fptlp-admin"><h1>'.esc_html($title).'</h1>'; settings_errors(); echo '<p>Shortcode: <code>[fpt_landing_page]</code></p><form action="options.php" method="post">'; settings_fields('fptlp_group'); return true;
    }
    public function enqueue_admin_assets($hook) {
        if (!in_array($hook, $this->admin_page_hooks, true) || !current_user_can('manage_options')) return;
        wp_enqueue_media();
        wp_enqueue_style('fptlp-admin', plugins_url('assets/fptlp-admin.css', __FILE__), [], '1.9.24');
        wp_enqueue_script('fptlp-admin', plugins_url('assets/fptlp-admin.js', __FILE__), ['jquery', 'media-editor'], '1.9.24', true);
    }
    private function render_banner_slider($s) {
        $slides=[];
        foreach (['banner_url','banner_slide_2','banner_slide_3','banner_slide_4','banner_slide_5','banner_slide_6'] as $field) {
            if (!empty($s[$field])) $slides[]=$s[$field];
        }
        if (!$slides) return;
        ?><div class="fptlp-banner-slider" role="region" aria-label="Banner ưu đãi" aria-roledescription="carousel">
        <div class="fptlp-banner-track"><?php foreach ($slides as $index=>$url): ?><div class="fptlp-banner-slide" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr(($index+1).' / '.count($slides)); ?>" <?php if ($index) echo 'hidden'; ?>><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($s['headline'].' — banner '.($index+1)); ?>" loading="<?php echo $index?'lazy':'eager'; ?>" <?php if (!$index) echo 'fetchpriority="high"'; ?>></div><?php endforeach; ?></div>
        <?php if (count($slides)>1): ?>
        <button class="fptlp-banner-arrow fptlp-banner-prev" type="button" aria-label="Banner trước"><span aria-hidden="true">‹</span></button>
        <button class="fptlp-banner-arrow fptlp-banner-next" type="button" aria-label="Banner tiếp theo"><span aria-hidden="true">›</span></button>
        <div class="fptlp-banner-dots" aria-label="Chọn banner"><?php foreach ($slides as $index=>$url): ?><button type="button" aria-label="Xem banner <?php echo $index+1; ?>" aria-pressed="<?php echo $index?'false':'true'; ?>"></button><?php endforeach; ?></div>
        <?php endif; ?></div><?php
    }

    private function image_field($name, $url, $label) {
        ?>
        <div class="fptlp-image-field">
            <input type="hidden" class="fptlp-image-value" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($url); ?>">
            <div class="fptlp-image-preview">
                <img <?php if ($url) echo 'src="'.esc_url($url).'"'; ?> alt="<?php echo esc_attr($label); ?>" <?php if (!$url) echo 'hidden'; ?>>
                <span class="fptlp-image-empty" <?php if ($url) echo 'hidden'; ?>>Chưa chọn ảnh</span>
            </div>
            <div class="fptlp-image-actions"><button type="button" class="button fptlp-pick-image"><?php echo $url ? 'Đổi ảnh' : 'Chọn ảnh'; ?></button>
            <button type="button" class="button-link-delete fptlp-remove-image" <?php if (!$url) echo 'hidden'; ?>>Gỡ ảnh</button></div>
        </div>
        <?php
    }
    private function package_editor($category, $index, $item = []) {
        $base = self::OPT.'[package_groups]['.$category.'][items]['.$index.']';
        $speed = preg_split('~\s*/\s*~u', $item['speed'] ?? '', 2);
        $item['download'] = $speed[0] ?? ''; $item['upload'] = $speed[1] ?? '';
        ?>
        <details class="fptlp-package-editor" open>
            <summary><span class="fptlp-package-title"><?php echo esc_html($item['name'] ?? 'Gói cước mới'); ?></span><span class="fptlp-edit-hint">Chỉnh sửa</span></summary>
            <div class="fptlp-package-body">
                <div class="fptlp-package-toolbar"><button type="button" class="button fptlp-move-up" aria-label="Chuyển gói lên">↑ Lên</button><button type="button" class="button fptlp-move-down" aria-label="Chuyển gói xuống">↓ Xuống</button><button type="button" class="button-link-delete fptlp-remove-package">Xóa gói</button></div>
                <div class="fptlp-package-layout"><div class="fptlp-fields-grid">
                <?php foreach (['name'=>'Tên gói cước','price'=>'Giá / tháng','download'=>'Tốc độ tải xuống','upload'=>'Tốc độ tải lên','badge'=>'Nhãn nổi bật','suitable_for'=>'Phù hợp với (theo quyền lợi thực tế)','detail'=>'Liên kết chi tiết'] as $field=>$label): ?>
                    <label class="fptlp-field"><span><?php echo esc_html($label); ?><?php if ($field === 'name') echo ' *'; ?></span><input type="<?php echo $field === 'detail' ? 'url' : 'text'; ?>" data-field="<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($base.'['.$field.']'); ?>" value="<?php echo esc_attr($item[$field] ?? ''); ?>" <?php if ($field === 'name') echo 'required'; ?> placeholder="<?php echo esc_attr(['name'=>'Ví dụ: Internet Giga','price'=>'195.000đ','download'=>'300 Mbps','upload'=>'300 Mbps','badge'=>'Ví dụ: Phổ biến','suitable_for'=>'Ví dụ: Gia đình cần Internet và truyền hình','detail'=>'https://...'][$field]); ?>"></label>
                <?php endforeach; ?>
                <p class="description fptlp-badge-help">Nhãn nên ngắn, khoảng 24 ký tự: Ưu đãi, Bán chạy, Cao cấp. Đưa nội dung khuyến mãi dài vào Quyền lợi; nhãn dài được thu gọn trên ảnh và hiện đầy đủ trong Xem nhanh.</p>
                <label class="fptlp-field"><span>Mức đồng hồ tốc độ</span><select data-field="meter_level" name="<?php echo esc_attr($base.'[meter_level]'); ?>">
                    <?php $level = empty($item['name']) ? 'speed' : $this->meter_level($item); ?>
                    <option value="speed" <?php selected($level,'speed'); ?>>Tự theo tốc độ — 30%, 80% hoặc 100%</option>
                    <option value="third" <?php selected($level,'third'); ?>>1/3 cung — giữ mức Giga cũ</option>
                    <option value="high" <?php selected($level,'high'); ?>>80% cung</option>
                    <option value="full" <?php selected($level,'full'); ?>>Đầy cung — các gói khác</option>
                </select><small class="description">Tự theo tốc độ: tải xuống và tải lên trên 300 Mbps → 100%; tải xuống trên 300, tải lên bằng 300 → 80%; còn lại → 30%. Mức chọn cố định được giữ khi đổi tên gói.</small></label>
                <fieldset class="fptlp-benefits"><legend>Quyền lợi của gói</legend><div class="fptlp-benefit-list">
                    <?php foreach (!empty($item['benefits']) ? $item['benefits'] : [''] as $benefit): ?>
                        <div class="fptlp-benefit-row"><input type="text" aria-label="Quyền lợi" name="<?php echo esc_attr($base.'[benefits][]'); ?>" value="<?php echo esc_attr($benefit); ?>" placeholder="Ví dụ: Modem Wi-Fi 6"><button type="button" class="button fptlp-remove-benefit" aria-label="Xóa quyền lợi">×</button></div>
                    <?php endforeach; ?>
                </div><button type="button" class="button fptlp-add-benefit">+ Thêm quyền lợi</button></fieldset>
                </div><div class="fptlp-package-image"><h3>Ảnh gói cước</h3><?php $this->image_field($base.'[image]', $item['image'] ?? '', 'Ảnh gói cước'); ?><p class="description">Nên dùng cùng tỷ lệ 62:45, ví dụ 1240 × 900 px; chừa khoảng trống trên cùng cho nhãn. Ảnh khác tỷ lệ được giữ trọn trong khung trắng cùng chiều cao, không cắt chữ.</p><p class="description fptlp-image-guidance" aria-live="polite"></p></div></div>
                <section class="fptlp-editor-section"><h3>Thiết bị / quà tặng đi kèm</h3><p class="description">Hiển thị trong phần Xem nhanh. Mỗi thiết bị có tên và ảnh riêng.</p>
                    <div class="fptlp-equipment-list"><?php foreach ($item['equipment'] ?? [] as $device_index=>$device) $this->equipment_editor($base, $device_index, $device); ?></div>
                    <button type="button" class="button fptlp-add-equipment">+ Thêm thiết bị</button>
                    <template class="fptlp-equipment-template"><?php $this->equipment_editor($base, '__ROW__'); ?></template>
                </section>
                <section class="fptlp-editor-section"><h3>Giá theo khu vực</h3><p class="description">Giá ở trên là giá tham khảo mặc định. Chỉ thêm khu vực bạn đã xác nhận giá; dùng cùng tên khu vực giữa các gói. Nếu chưa có giá riêng, khách sẽ thấy giá tham khảo kèm thông báo.</p>
                    <div class="fptlp-region-list"><?php foreach ($item['region_prices'] ?? [] as $region_index=>$region) $this->region_price_editor($base, $region_index, $region); ?></div>
                    <button type="button" class="button fptlp-add-region">+ Thêm giá khu vực</button>
                    <template class="fptlp-region-template"><?php $this->region_price_editor($base, '__ROW__'); ?></template>
                </section>
            </div>
        </details>
        <?php
    }
    private function equipment_editor($base, $index, $device = []) {
        $name = $base.'[equipment]['.$index.']'; ?>
        <div class="fptlp-equipment-row">
            <label class="fptlp-field"><span>Tên thiết bị / quà tặng</span><input type="text" name="<?php echo esc_attr($name.'[title]'); ?>" value="<?php echo esc_attr($device['title'] ?? ''); ?>" placeholder="Ví dụ: Modem Wi-Fi 6"></label>
            <?php $this->image_field($name.'[image]', $device['image'] ?? '', 'Ảnh thiết bị đi kèm'); ?>
            <button type="button" class="button-link-delete fptlp-remove-equipment">Xóa thiết bị</button>
        </div><?php
    }
    private function region_price_editor($base, $index, $region = []) {
        $name = $base.'[region_prices]['.$index.']'; ?>
        <div class="fptlp-region-row">
            <label class="fptlp-field"><span>Khu vực</span><input type="text" name="<?php echo esc_attr($name.'[label]'); ?>" value="<?php echo esc_attr($region['label'] ?? ''); ?>" placeholder="Ví dụ: Hà Nội nội thành"></label>
            <label class="fptlp-field"><span>Giá / tháng tại khu vực</span><input type="text" name="<?php echo esc_attr($name.'[price]'); ?>" value="<?php echo esc_attr($region['price'] ?? ''); ?>" placeholder="Nhập giá đã xác nhận"></label>
            <button type="button" class="button-link-delete fptlp-remove-region">Xóa khu vực</button>
        </div><?php
    }
    public function packages_page($business_only = false) {
        if (!$this->admin_start($business_only ? 'Gói cước doanh nghiệp' : 'Quản lý gói cước')) return;
        $s=$this->settings(); $groups=$this->current_package_groups($s); $categories=$this->package_categories();
        if ($business_only) $categories = ['business'=>$categories['business']]; else unset($categories['business']);
        if ($business_only) $this->business_admin_fields($s);
        $selected=isset($categories[$s['package_default_category']]) && $s['package_default_category']!=='combo-vvip' ? $s['package_default_category'] : 'internet';
        ?>
        <p>Mỗi gói có các trường riêng. Điền thông tin, chọn ảnh rồi bấm lưu. Chỉ 6 gói đầu tiên trong mỗi nhóm hiển thị trên landing page.</p>
        <?php if (!$business_only): ?><p><label for="fptlp_first_group"><strong>Nhóm mở sẵn trong thanh tab bên dưới</strong></label> <select id="fptlp_first_group" name="<?php echo esc_attr(self::OPT); ?>[package_default_category]">
        <?php foreach ($categories as $key=>$info): if ($key==='combo-vvip') continue; ?><option value="<?php echo esc_attr($key); ?>" <?php selected($selected,$key); ?>><?php echo esc_html($info['label']); ?></option><?php endforeach; ?></select></p><?php endif; ?>
        <div class="fptlp-admin-tabs" aria-label="Nhóm gói cước">
        <?php foreach ($categories as $key=>$info): ?><button type="button" class="button fptlp-admin-tab" data-admin-tab="<?php echo esc_attr($key); ?>" aria-controls="fptlp-panel-<?php echo esc_attr($key); ?>"><?php echo esc_html($info['label']); ?></button><?php endforeach; ?>
        </div>
        <?php foreach ($categories as $key=>$info): $items=$this->parse_packages($groups[$key] ?? ''); ?>
        <section class="fptlp-admin-category" id="fptlp-panel-<?php echo esc_attr($key); ?>" data-admin-panel="<?php echo esc_attr($key); ?>">
            <h2><?php echo esc_html($info['label']); ?> <span class="fptlp-package-count"></span></h2><p><?php echo esc_html($info['description']); ?></p>
            <input type="hidden" name="<?php echo esc_attr(self::OPT.'[package_groups]['.$key.'][present]'); ?>" value="1">
            <div class="fptlp-package-list"><?php foreach ($items as $index=>$item) $this->package_editor($key, $index, $item); ?></div>
            <p class="fptlp-empty-group">Chưa có gói cước. Bấm “Thêm gói cước” để bắt đầu.</p>
            <p class="fptlp-limit-note">Chỉ 6 gói đầu hiển thị. Bạn có thể đổi thứ tự để chọn gói xuất hiện.</p>
            <button type="button" class="button button-secondary fptlp-add-package">+ Thêm gói cước</button>
            <template class="fptlp-package-template"><?php $this->package_editor($key, '__INDEX__'); ?></template>
        </section>
        <?php endforeach; ?>
        <input type="hidden" name="<?php echo esc_attr(self::OPT); ?>[package_editor_complete]" value="1">
        <?php if ($business_only): ?><input type="hidden" name="<?php echo esc_attr(self::OPT); ?>[content_complete][business]" value="1"><?php endif; ?>
        <p class="fptlp-save-bar"><?php submit_button('Lưu tất cả gói cước', 'primary', 'submit', false); ?> <span>Thay đổi chỉ được áp dụng sau khi lưu.</span></p>
        <p class="fptlp-admin-status screen-reader-text" aria-live="polite"></p>
        <?php echo '</form></div>';
    }
    public function banner_page() {
        if (!$this->admin_start('Quản lý ảnh banner')) return; $s=$this->settings(); ?>
        <div class="fptlp-banner-card"><h2>Banner dạng slide đầu landing page</h2><p>Quản lý toàn bộ ảnh banner tại đây. Chọn ảnh từ thư viện hoặc tải ảnh mới lên; ảnh được hiển thị theo thứ tự từ trên xuống.</p>
        <table class="form-table" role="presentation">
        <tr><th>Ảnh banner đầu trang</th><td><?php $this->image_field(self::OPT.'[banner_url]', $s['banner_url'], 'Ảnh banner'); ?></td></tr>
        <?php foreach (range(2,6) as $n): ?><tr><th>Banner slide <?php echo (int)$n; ?></th><td><?php $this->image_field(self::OPT.'[banner_slide_'.$n.']', $s['banner_slide_'.$n], 'Ảnh slide '.$n); ?><p class="description">Để trống để bỏ qua. Dùng ảnh cùng tỷ lệ với banner đầu tiên để chuyển ảnh ổn định.</p></td></tr><?php endforeach; ?>
        </table><p class="description">Có thể dùng tối đa 6 ảnh. Ô trống được bỏ qua; một ảnh sẽ ẩn nút chuyển slide. Gỡ tất cả ảnh để ẩn banner. Bấm Lưu banner để áp dụng.</p></div>
        <?php submit_button('Lưu banner'); echo '</form></div>';
    }
    public function registration_page() {
        if (!$this->admin_start('Quản lý đăng ký & thông báo')) return; $s=$this->settings(); ?>
        <table class="form-table"><tr><th>Tiêu đề đăng ký</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[registration_heading]" value="<?php echo esc_attr($s['registration_heading']); ?>"></td></tr>
        <tr><th>Email nhận thông báo</th><td><input type="email" class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[notification_email]" value="<?php echo esc_attr($s['notification_email']); ?>"><p class="description">Email nhận thông báo về khách mới. Nếu không nhận được hãy kiểm tra cấu hình gửi mail SMTP của hosting.</p></td></tr></table>
        <p><label><strong>Google Ads send_to (không bắt buộc)</strong><br><input class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[ads_send_to]" value="<?php echo esc_attr($s['ads_send_to']); ?>" placeholder="AW-123456789/AbCdEf"></label></p><p class="description">Chỉ điền khi dùng Google tag đã cài trên website. Nếu cấu hình qua GTM, để trống và dùng sự kiện fptlp_lead_saved; chỉ chọn một cách để tránh đếm trùng. Mã này không tự cài Google tag hoặc thay đổi cài đặt đồng ý cookie.</p>
        <p>Link gọn: <code>/landing-fpt/internet/</code>, <code>/landing-fpt/combo-tv/</code>, <code>/landing-fpt/combo-vvip/</code>, <code>/landing-fpt/gaming/</code>. Dùng chung trang có đường dẫn landing-fpt; không cần tạo thêm trang con. Sau cập nhật hãy mở một trang quản trị và xóa cache. Nếu báo 404, vào Cài đặt → Đường dẫn tĩnh → Lưu thay đổi. Trang con thật trùng đường dẫn được ưu tiên.</p><p>Link quảng cáo cũ vẫn hỗ trợ: thêm <code>?fptlp_group=internet</code>, <code>?fptlp_group=combo-tv</code>, <code>?fptlp_group=combo-vvip</code> hoặc <code>?fptlp_group=gaming</code>. Nếu URL đã có dấu ?, dùng &amp;fptlp_group=...</p>
        <p><strong>Dữ liệu khách hàng:</strong> FPT Landing Page → Khách đăng ký FPT. Mỗi yêu cầu được lưu trong cơ sở dữ liệu WordPress trước khi thử gửi email.</p>
        <p><a class="button button-secondary" href="<?php echo esc_url(admin_url('edit.php?post_type=fptlp_lead')); ?>">Xem khách hàng đã đăng ký</a></p>
        <?php submit_button('Lưu cài đặt đăng ký'); echo '</form></div>';
    }
    public function register_assets() {
        wp_register_style('fptlp-style', plugins_url('assets/fptlp.css', __FILE__), [], '1.9.24');
        wp_register_script('fptlp-script', plugins_url('assets/fptlp.js', __FILE__), [], '1.9.24', true);
        wp_register_style('fptlp-catalog', plugins_url('assets/fptlp-catalog.css', __FILE__), ['fptlp-style'], '1.9.24');
        wp_register_script('fptlp-catalog', plugins_url('assets/fptlp-catalog.js', __FILE__), ['fptlp-script'], '1.9.24', true);
        wp_register_style('fptlp-content', plugins_url('assets/fptlp-content.css', __FILE__), ['fptlp-catalog'], '1.9.24');
        wp_register_script('fptlp-content', plugins_url('assets/fptlp-content.js', __FILE__), ['fptlp-catalog'], '1.9.24', true);
        // Detect normal content, synced blocks and Elementor templates before wp_head.
        if ($this->should_load_assets()) $this->enqueue_landing_assets();
    }
    private function package_fit($item) {
        if (!empty($item['suitable_for'])) return $item['suitable_for'];
        foreach ($item['benefits'] ?? [] as $benefit) if (preg_match('/^Phù hợp\s+(?:với\s+)?(.+)$/ui',$benefit,$m)) return $m[1];
        return '';
    }
    private function meter_level($item) {
        if (isset($item['meter_level']) && in_array($item['meter_level'], ['third','high','full','speed'], true)) return $item['meter_level'];
        $name = isset($item['name']) && is_scalar($item['name']) ? (string)$item['name'] : '';
        return preg_match('/(?:^|[^\p{L}\p{N}])giga(?:$|[^\p{L}\p{N}])/iu', $name) ? 'third' : 'full';
    }
    private function speed_mbps($value) {
        if (!is_string($value) || !preg_match('/^\s*([0-9]+(?:[.,][0-9]+)?)\s*(Mbps|Gbps)\s*$/iu',$value,$matches)) return null;
        $number=(float)str_replace(',','.',$matches[1])*(strtolower($matches[2])==='gbps'?1000:1);
        return is_finite($number) && $number>=0 && $number<=1000000 ? $number : null;
    }
    private function render_speed_meter($item,$download,$upload) {
        $level=$this->meter_level($item);$down=$this->speed_mbps($download);$up=$this->speed_mbps($upload);
        $target=$level==='third'?1/3:($level==='high'?.8:1);
        if ($level==='speed') $target=$down!==null && $up!==null ? ($down>300 && $up>300 ? 1 : ($down>300 && $up==300 ? .8 : .3)) : .3;
        $angle=-180+180*$target;$radians=deg2rad($angle);
        $path=sprintf('M8 50 A42 42 0 0 1 %.5F %.5F',50+42*cos($radians),50+42*sin($radians));
        $gradient=wp_unique_id('fptlp-meter-gradient-'); ?>
        <svg class="fptlp-speed-meter" data-fptlp-meter="<?php echo esc_attr($level); ?>" data-target="<?php echo esc_attr($target); ?>" data-down="<?php echo $down===null?'':esc_attr($down); ?>" data-up="<?php echo $up===null?'':esc_attr($up); ?>" viewBox="0 0 100 62" width="58" height="36" aria-hidden="true" focusable="false">
            <defs><linearGradient id="<?php echo esc_attr($gradient); ?>" gradientUnits="userSpaceOnUse" x1="8" y1="50" x2="92" y2="50"><?php for($i=0;$i<=32;$i++):$t=$i/32; ?><stop offset="<?php echo esc_attr((1-cos(M_PI*$t))*50); ?>%" stop-color="<?php echo esc_attr('rgb('.round(255-23*$t).','.round(190-102*$t).','.round(112-112*$t).')'); ?>"/><?php endfor; ?></linearGradient></defs>
            <path class="fptlp-meter-track" d="M8 50 A42 42 0 0 1 92 50"/>
            <path class="fptlp-meter-fill" stroke="url(#<?php echo esc_attr($gradient); ?>)" d="<?php echo esc_attr($path); ?>"/>
            <path class="fptlp-needle" transform="rotate(<?php echo esc_attr($angle); ?> 50 50)" d="M51.8 42.9 L76 49.1 Q78 50 76 50.9 L51.8 57.1 A7.325 7.325 0 1 1 51.8 42.9 Z"/>
        </svg><?php
    }
    private function parse_packages($raw) {
        if (is_array($raw)) {
            $packages = [];
            foreach ($raw as $item) {
                if (!is_array($item) || !isset($item['name']) || trim((string)$item['name']) === '') continue;
                $item['speed'] = ($item['download'] ?? '').' / '.($item['upload'] ?? '');
                $packages[] = $item;
            }
            return $packages;
        }
        $packages = []; foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) { $p = array_map('trim', explode('|', $line)); if (count($p) < 4 || $p[0] === '') continue; $packages[] = ['name'=>$p[0], 'price'=>$p[1] ?? '', 'speed'=>$p[2] ?? '', 'benefits'=>array_values(array_filter(array_map('trim', explode(';', $p[3] ?? '')), static function($benefit) { return $benefit !== ''; })), 'badge'=>$p[4] ?? '', 'image'=>isset($p[5]) ? esc_url_raw($p[5]) : '', 'detail'=>isset($p[6]) ? esc_url_raw($p[6]) : '']; } return $packages;
    }
    private function region_prices($item) {
        $prices = [];
        foreach ($item['region_prices'] ?? [] as $region) {
            $key = sanitize_title($region['label']);
            if ($key !== '' && $region['price'] !== '') $prices[$key] = ['label'=>$region['label'], 'price'=>$region['price']];
        }
        return $prices;
    }
    private function available_regions($groups) {
        $regions = [];
        foreach ($groups as $raw) foreach (array_slice($this->parse_packages($raw), 0, 6) as $item) {
            foreach ($this->region_prices($item) as $key=>$region) if (!isset($regions[$key])) $regions[$key] = $region['label'];
        }
        return $regions;
    }
    private function lead_quote($package_key, $region_key, $expected_name = '') {
        $groups = $this->current_package_groups($this->settings());
        $regions = $this->available_regions($groups);
        $quote = ['region'=>$regions[$region_key] ?? '', 'package'=>'', 'price'=>'', 'basis'=>''];
        $parts = explode(':', $package_key, 2);
        if (count($parts)===2 && $parts[0]==='table' && preg_match('/^(?:[0-9]|[12][0-9])$/D',$parts[1])) {
            $table=$this->content_settings($this->settings())['price_table'];
            $row=$table['plans'][(int)$parts[1]] ?? null;
            if ($this->settings()['sync_price_table']!=='1' && $table['enabled']==='1' && $row && $expected_name===$row['name']) {
                $quote['package']=$row['name'];$quote['price']=$row['price'];$quote['basis']='Giá tham khảo bảng tổng hợp';
            }
            return $quote;
        }
        if (count($parts) !== 2 || !isset($this->package_categories()[$parts[0]]) || !preg_match('/^[0-5]$/', $parts[1]) || (int)$parts[1] > 5) return $quote;
        $items = $this->parse_packages($groups[$parts[0]] ?? '');
        if (!isset($items[(int)$parts[1]])) return $quote;
        $item = $items[(int)$parts[1]];
        if ($expected_name !== '' && $expected_name !== $item['name']) return $quote;
        $prices = $this->region_prices($item);
        $specific = isset($prices[$region_key]);
        $quote['package'] = $item['name'];
        $quote['price'] = $specific ? $prices[$region_key]['price'] : $item['price'];
        $quote['basis'] = $specific ? 'Giá khu vực' : 'Giá tham khảo mặc định';
        return $quote;
    }
    private function quick_view($item) { ?>
        <section class="fptlp-quick-panel" hidden tabindex="-1" aria-label="<?php echo esc_attr('Chi tiết nhanh '.$item['name']); ?>">
            <header class="fptlp-quick-header"><span>CHI TIẾT GÓI CƯỚC</span><button type="button" class="fptlp-quick-close">← Quay lại</button></header>
            <div class="fptlp-quick-content">
                <h3><?php echo esc_html($item['name']); ?></h3>
                <p class="fptlp-quick-price"><strong data-fptlp-price><?php echo esc_html($item['price']); ?></strong> <small>/tháng</small></p>
                <p class="fptlp-region-note" data-fptlp-price-note hidden></p>
                <?php if (!empty($item['badge'])): ?><p class="fptlp-quick-badge"><?php echo esc_html($item['badge']); ?></p><?php endif; ?>
                <p class="fptlp-quick-speed">Tốc độ: <?php echo esc_html($item['speed']); ?></p>
                <?php if (!empty($item['equipment'])): ?><h4>Thiết bị / quà tặng đi kèm</h4><div class="fptlp-equipment-grid">
                    <?php foreach ($item['equipment'] as $device): ?><figure><?php if (!empty($device['image'])): ?><img src="<?php echo esc_url($device['image']); ?>" alt="<?php echo esc_attr($device['title']); ?>" loading="lazy" decoding="async" width="160" height="120"><?php endif; ?><figcaption><?php echo esc_html($device['title']); ?></figcaption></figure><?php endforeach; ?>
                </div><?php endif; ?>
                <?php if (!empty($item['benefits'])): ?><h4>Quyền lợi của gói</h4><ul class="fptlp-quick-benefits"><?php foreach ($item['benefits'] as $benefit): ?><li><?php echo esc_html($benefit); ?></li><?php endforeach; ?></ul><?php endif; ?>
                <?php if (!empty($item['detail'])): ?><a class="fptlp-full-detail" href="<?php echo esc_url($item['detail']); ?>">Xem trang chi tiết ↗</a><?php endif; ?>
            </div>
            <footer><button type="button" class="fptlp-btn fptlp-popup-open" data-modal="register" data-package="<?php echo esc_attr($item['name']); ?>">Đăng ký gói này</button></footer>
        </section>
        <?php
    }
    private function render_package_cards($key, $info, $items) { ?>
                <?php if (!$items): ?>
                  <div class="fptlp-empty-category"><p>Đang cập nhật gói cước cho mục này. Bạn có thể để lại số điện thoại để được tư vấn.</p><button type="button" class="fptlp-popup-open" data-modal="callback">Yêu cầu gọi lại</button></div>
                <?php else: ?>
                <div class="fptlp-carousel" aria-label="<?php echo esc_attr('Danh sách '.$info['label']); ?>">
                  <button type="button" class="fptlp-nav fptlp-prev" aria-label="Xem gói trước" disabled>‹</button>
                  <div class="fptlp-grid" tabindex="0" role="region" aria-roledescription="carousel" aria-label="<?php echo esc_attr('Vuốt ngang để xem '.$info['label']); ?>">
                  <?php foreach ($items as $package_index=>$p):
                    $speed_parts=preg_split('~\s*/\s*~u',$p['speed'],2);
                    $download=trim($speed_parts[0] ?? $p['speed']); $upload=trim($speed_parts[1] ?? '');
                  ?>
                    <article class="fptlp-card fptlp-card--banner" data-fptlp-package-key="<?php echo esc_attr($key.':'.$package_index); ?>" data-fptlp-base-price="<?php echo esc_attr($p['price']); ?>" data-fptlp-prices="<?php echo esc_attr(wp_json_encode($this->region_prices($p))); ?>">
                      <?php if (!empty($p['badge'])): ?><div class="fptlp-card-topnote" title="<?php echo esc_attr($p['badge']); ?>"><?php echo esc_html($p['badge']); ?></div><?php endif; ?>
                      <div class="fptlp-card-banner">
                        <?php if (!empty($p['image'])): ?><img src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr('Ảnh gói '.$p['name']); ?>" loading="lazy" decoding="async" width="520" height="310">
                        <?php else: ?><div class="fptlp-device fptlp-device--banner" aria-hidden="true"><span>FPT</span><i></i><i></i></div><?php endif; ?>
                      </div>
                      <div class="fptlp-card-body">
                        <div class="fptlp-card-heading">
                          <div class="fptlp-card-summary">
                            <h3 title="<?php echo esc_attr($p['name']); ?>"><?php echo esc_html($p['name']); ?></h3>
                            <div class="fptlp-price"><span data-fptlp-price><?php echo esc_html($p['price']); ?></span><small>/tháng</small></div><p class="fptlp-region-note" data-fptlp-price-note hidden></p>
                          </div>
                        </div>
                        <?php $fit=$this->package_fit($p); if ($fit!==''): ?><p class="fptlp-suitable"><strong>Phù hợp với:</strong> <?php echo esc_html($fit); ?></p><?php else: ?><p class="fptlp-suitable">Tư vấn lựa chọn theo nhu cầu và địa chỉ lắp đặt.</p><?php endif; ?>
                        <div class="fptlp-speed-box" role="img" aria-label="<?php echo esc_attr('Tốc độ tải xuống '.$download.($upload!==''?', tải lên '.$upload:'')); ?>"><div class="fptlp-speed-copy" aria-hidden="true"><small>Tốc độ (Download / Upload)</small><strong><span class="fptlp-dl">↓ <span class="fptlp-speed-number"><?php echo esc_html($download); ?></span></span><?php if ($upload !== ''): ?><span class="fptlp-speed-divider">|</span><span class="fptlp-ul">↑ <span class="fptlp-speed-number"><?php echo esc_html($upload); ?></span></span><?php endif; ?></strong></div>
                          <?php $this->render_speed_meter($p,$download,$upload); ?>
                        </div>
                        <div class="fptlp-benefits-wrap"><ul class="fptlp-benefits"><?php foreach ($p['benefits'] as $idx=>$benefit): ?><li<?php echo $idx >= 4 ? ' class="fptlp-benefit-extra" hidden' : ''; ?>><?php echo esc_html($benefit); ?></li><?php endforeach; ?></ul></div>
                        <div class="fptlp-actions">
                          <button type="button" class="fptlp-btn fptlp-btn-card fptlp-popup-open" data-modal="register" data-package="<?php echo esc_attr($p['name']); ?>">Đăng ký ngay</button>
                          <button type="button" class="fptlp-outline fptlp-quick-open" aria-expanded="false">Xem nhanh</button>
                        </div>
                      </div>
                      <?php $this->quick_view($p); ?>
                    </article>
                  <?php endforeach; ?>
                  </div>
                  <button type="button" class="fptlp-nav fptlp-next" aria-label="Xem gói tiếp theo">›</button>
                </div>
                <p class="fptlp-slide-count" aria-live="off">Hiển thị tối đa 6 gói trong nhóm</p>
                <?php endif; ?>

        <?php
    }
    public function shortcode() {
        $s = $this->settings();
        if (!wp_style_is('fptlp-style','registered')) $this->register_assets();
        $this->enqueue_landing_assets();
        $groups = $this->current_package_groups($s); $categories = $this->package_categories(); unset($categories['business']);
        if ($s['hide_empty_categories']==='1') foreach ($categories as $key=>$info) if (!$this->parse_packages($groups[$key] ?? '')) unset($categories[$key]);
        $vvip_info=$categories['combo-vvip'] ?? null; unset($categories['combo-vvip']);
        $fallback=isset($categories['internet'])?'internet':(array_key_first($categories) ?? '');
        $default_category = isset($categories[$s['package_default_category'] ?? '']) ? $s['package_default_category'] : $fallback; $tab_uid = wp_unique_id('fptlp-tabs-'); $form_action = esc_url(admin_url('admin-post.php')); $modal_uid = wp_unique_id('fptlp-modal-'); $regions = $this->available_regions($groups);
        $route_group=$this->campaign_group();
        $landing_return_url=$route_group!=='' ? home_url('/landing-fpt/'.$route_group.'/') : get_permalink();
        if (isset($categories[$route_group])) $default_category=$route_group;
        ob_start();
        // Late templates still receive styles before the markup; WordPress avoids duplicates.
        if (did_action('wp_head')) wp_print_styles(['fptlp-style','fptlp-catalog','fptlp-content']); ?>
        <div class="fptlp" data-campaign-group="<?php echo esc_attr($route_group); ?>" data-ads-send-to="<?php echo esc_attr($s['ads_send_to']); ?>" style="--fptlp-accent:<?php echo esc_attr($s['accent']); ?>">
          <?php
          $status=isset($_GET['fptlp_status']) && is_string($_GET['fptlp_status']) ? sanitize_key($_GET['fptlp_status']) : '';
          $messages=['success'=>'Cảm ơn bạn! Thông tin đã được ghi nhận.','duplicate'=>'Yêu cầu này đã được ghi nhận trước đó. Bạn không cần gửi lại.','invalid_phone'=>'Số điện thoại chưa hợp lệ. Vui lòng nhập số di động hoặc số cố định Việt Nam, có thể dùng +84.','expired'=>'Phiên đăng ký đã hết hạn. Vui lòng tải lại trang rồi thử lại.','rate_limited'=>'Bạn đã gửi nhiều yêu cầu trong thời gian ngắn. Vui lòng thử lại sau 10 phút hoặc gọi hotline.','busy'=>'Yêu cầu đang được xử lý. Vui lòng chờ một chút trước khi gửi lại.','error'=>'Chưa gửi được thông tin. Vui lòng thử lại hoặc gọi hotline.'];
          if (isset($messages[$status])): $ok=in_array($status,['success','duplicate'],true); ?><div id="fptlp-feedback" tabindex="-1" class="<?php echo $ok?'fptlp-success':'fptlp-error'; ?>" role="<?php echo $ok?'status':'alert'; ?>"><?php echo esc_html($messages[$status]); ?></div><?php endif; ?>

          <section class="fptlp-hero fptlp-hero--stacked"><?php $this->render_banner_slider($s); ?><div class="fptlp-hero-copy"><span class="fptlp-eyebrow"><?php echo esc_html($s['eyebrow']); ?></span><h1><?php echo esc_html($s['headline']); ?></h1><?php if ($s['service_area']!==''): ?><p class="fptlp-service-area">Khu vực phục vụ: <?php echo esc_html($s['service_area']); ?></p><?php endif; ?><p><?php echo esc_html($s['intro']); ?></p><p class="fptlp-hero-note"><?php echo esc_html($s['hero_note']); ?></p><div class="fptlp-hero-actions"><button type="button" class="fptlp-btn fptlp-popup-open" data-modal="register"><?php echo esc_html($s['hero_cta'] ?: 'Nhận báo giá tại địa chỉ của bạn'); ?></button><button type="button" class="fptlp-hero-callback fptlp-popup-open" data-modal="callback">Chưa biết chọn gói? Nhờ tư vấn</button></div><?php if ($s['trust_1']!=='' || $s['trust_2']!=='' || $s['trust_3']!==''): ?><div class="fptlp-trust"><?php foreach (['trust_1','trust_2','trust_3'] as $field) if ($s[$field]!==''): ?><span>✓ <?php echo esc_html($s[$field]); ?></span><?php endif; ?></div><?php endif; ?></div></section>

          <?php if ($s['wpforms_shortcode'] !== '' && shortcode_exists('wpforms')):
              $embedded_form = do_shortcode($s['wpforms_shortcode']);
              if (trim($embedded_form) !== ''): ?>
          <section class="fptlp-embedded-form" aria-label="Đăng ký tư vấn">
            <?php if ($s['wpforms_heading'] !== ''): ?><h2><?php echo esc_html($s['wpforms_heading']); ?></h2><?php endif; ?>
            <?php if ($s['wpforms_intro'] !== ''): ?><p class="fptlp-embedded-intro"><?php echo esc_html($s['wpforms_intro']); ?></p><?php endif; ?>
            <?php echo $embedded_form; ?>
          </section>
          <?php endif; elseif ($s['wpforms_shortcode'] !== '' && current_user_can('manage_options')): ?>
          <p>Vui lòng kích hoạt WPForms để hiển thị form đăng ký.</p>
          <?php endif; ?>

          <?php if ($categories || $regions || $vvip_info): ?><section class="fptlp-packages" aria-label="Các nhóm gói cước FPT">
            <?php if ($regions): ?><div class="fptlp-region-picker"><label><span>Khu vực lắp đặt</span><select data-fptlp-region><option value="">Xem giá tham khảo</option><?php foreach ($regions as $region_key=>$region_label): ?><option value="<?php echo esc_attr($region_key); ?>"><?php echo esc_html($region_label); ?></option><?php endforeach; ?></select></label><p data-fptlp-region-status role="status">Chọn khu vực để xem giá đã được cập nhật.</p></div><?php endif; ?>
          <?php if ($vvip_info): ?>
            <?php $vvip_style=''; foreach (['vvip_background','vvip_background_end','vvip_border','vvip_label_color','vvip_title_color','vvip_description_color'] as $color_key) { $color_value=$s[$color_key]; if (!is_string($color_value) || !preg_match('/^#[0-9a-fA-F]{6}$/D',$color_value)) $color_value=$this->defaults()[$color_key]; $vvip_style.='--'.str_replace('_','-',$color_key).':'.$color_value.';'; } ?>
            <section class="fptlp-vvip-feature" style="<?php echo esc_attr($vvip_style); ?>" aria-label="<?php echo esc_attr($s['vvip_title'] ?: 'Gói Combo Wi-Fi – Truyền hình V.VIP'); ?>">
              <?php if ($s['vvip_label']!=='' || $s['vvip_title']!==''): ?><div class="fptlp-vvip-bar"><?php if ($s['vvip_label']!==''): ?><span><?php echo esc_html($s['vvip_label']); ?></span><?php endif; ?><?php if ($s['vvip_title']!==''): ?><h2><?php echo esc_html($s['vvip_title']); ?></h2><?php endif; ?></div><?php endif; ?>
              <?php $vvip_description=trim($s['vvip_description'].($s['vvip_show_notice']==='1'?' '.$s['notice']:'')); if ($vvip_description!==''): ?><p class="fptlp-vvip-intro"><?php echo esc_html($vvip_description); ?></p><?php endif; ?>
              <div class="fptlp-tab-panel fptlp-tab-panel--vvip" data-fptlp-category="combo-vvip">
                <?php $this->render_package_cards('combo-vvip',$vvip_info,array_slice($this->parse_packages($groups['combo-vvip'] ?? ''),0,6)); ?>
              </div>
            </section>
          <?php endif; ?>
          <?php if ($categories): ?>
            <div class="fptlp-package-tabs" role="tablist" aria-label="Chọn dịch vụ Internet FPT">
              <?php foreach ($categories as $key=>$info): $active=$key===$default_category; ?>
                <button type="button" class="fptlp-package-tab<?php echo $active ? ' is-active' : ''; ?>" role="tab" id="<?php echo esc_attr($tab_uid.'-'.$key.'-tab'); ?>" aria-controls="<?php echo esc_attr($tab_uid.'-'.$key.'-panel'); ?>" aria-selected="<?php echo $active ? 'true' : 'false'; ?>" tabindex="<?php echo $active ? '0' : '-1'; ?>"><?php echo esc_html($info['label']); ?></button>
              <?php endforeach; ?>
            </div>
            <?php foreach ($categories as $key=>$info):
                $active=$key===$default_category;
                $items=array_slice($this->parse_packages($groups[$key] ?? ''),0,6);
            ?>
              <div class="fptlp-tab-panel<?php echo $key === 'combo-vvip' ? ' fptlp-tab-panel--vvip' : ''; ?>" data-fptlp-category="<?php echo esc_attr($key); ?>" role="tabpanel" id="<?php echo esc_attr($tab_uid.'-'.$key.'-panel'); ?>" aria-labelledby="<?php echo esc_attr($tab_uid.'-'.$key.'-tab'); ?>" <?php if (!$active) echo 'hidden'; ?> >
                <div class="fptlp-section-head"><span class="fptlp-eyebrow"><?php echo $key === 'combo-vvip' ? 'INTERNET + TRUYỀN HÌNH FPT PLAY' : 'GỢI Ý DÀNH CHO BẠN'; ?></span><h2><?php echo esc_html($info['title']); ?></h2><p><?php echo esc_html($info['description']); ?> <?php echo esc_html($s['notice']); ?></p></div>
                <?php $this->render_package_cards($key, $info, $items); ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?></section><?php endif; ?>
          <?php $this->render_info($s); ?>
          <?php $this->render_business($s, $groups, $tab_uid); ?>
          <?php $this->render_article($s); ?>
          <?php $this->render_price_table($s); ?>
          <?php $this->render_faq($s); ?>
          <div class="fptlp-modal" hidden><div class="fptlp-modal-shade" data-close-modal></div><div class="fptlp-modal-panel" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($modal_uid); ?>-title" tabindex="-1"><button type="button" class="fptlp-modal-close" aria-label="Đóng" data-close-modal>×</button><h2 id="<?php echo esc_attr($modal_uid); ?>-title" data-fptlp-modal-title><?php echo esc_html($s['registration_heading'] ?: 'Đăng ký wifi FPT'); ?></h2>
          <form id="<?php echo esc_attr($modal_uid); ?>-form" class="fptlp-form fptlp-popup-form" method="post" action="<?php echo $form_action; ?>"><input type="hidden" name="action" value="fptlp_submit"><?php echo '<input type="hidden" name="fptlp_nonce" value="'.esc_attr(wp_create_nonce('fptlp_submit')).'">'; ?><input type="hidden" name="fptlp_package" value=""><input type="hidden" name="fptlp_kind" value="register"><input type="hidden" name="fptlp_region" value=""><input type="hidden" name="fptlp_package_key" value=""><input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($landing_return_url); ?>"><input type="hidden" name="redirect_to" value="<?php echo esc_url($landing_return_url); ?>">
          <div class="fptlp-form-result" role="status" tabindex="-1" hidden></div><label>Số điện thoại <span>*</span><input name="fptlp_phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="24" placeholder="Nhập số điện thoại"></label>
          <div class="fptlp-register-extra"><label>Họ và tên (không bắt buộc)<input name="fptlp_name" autocomplete="name" maxlength="100" placeholder="Nhập họ tên"></label><label>Địa chỉ lắp đặt (không bắt buộc)<input name="fptlp_address" maxlength="250" placeholder="Phường/xã, địa chỉ lắp đặt"></label><div class="fptlp-service-title">Dịch vụ cần tư vấn</div><div class="fptlp-service-options"><label><input type="checkbox" name="fptlp_services[]" value="Internet" checked> + Internet</label><label><input type="checkbox" name="fptlp_services[]" value="Truyền hình"> + Truyền hình</label><label><input type="checkbox" name="fptlp_services[]" value="Camera"> + Camera</label></div></div>
          <label class="fptlp-honey" aria-hidden="true">Không điền ô này<input name="fptlp_website" tabindex="-1" autocomplete="off"></label><button class="fptlp-popup-submit" type="submit">Đăng ký nhanh ›</button><p class="fptlp-form-consent">Gửi thông tin để được liên hệ tư vấn về dịch vụ.</p></form></div></div>
        </div><?php return ob_get_clean();
    }
    public function submit() {
        $status=$this->process_registration($_POST,$_SERVER);
        if (isset($_POST['fptlp_ajax']) && $_POST['fptlp_ajax']==='1') {
            nocache_headers();
            wp_send_json(['status'=>$status,'conversion_id'=>$status==='success'?$this->saved_conversion_id:'']);
        }
        $this->redirect_status($status);
    }
    private function redirect_status($status) { $url = wp_get_referer(); if (!$url) $url = home_url('/'); wp_safe_redirect(add_query_arg('fptlp_status', $status, remove_query_arg('fptlp_status', preg_replace('/#.*$/','',$url))).'#fptlp-feedback'); exit; }
}
new FPT_Landing_Page();
register_deactivation_hook(__FILE__, function() { wp_clear_scheduled_hook('fptlp_guard_cleanup'); });

add_filter('manage_fptlp_lead_posts_columns', function($cols) { return ['cb'=>$cols['cb'], 'title'=>'Khách hàng / SĐT', 'fptlp_package'=>'Gói quan tâm', 'fptlp_address'=>'Địa chỉ', 'fptlp_mail'=>'Thông báo email', 'date'=>'Ngày nhận']; });
add_action('manage_fptlp_lead_posts_custom_column', function($column, $post_id) { if ($column==='fptlp_package') echo esc_html(get_post_meta($post_id,'_fptlp_package',true)); if ($column==='fptlp_address') echo esc_html(get_post_meta($post_id,'_fptlp_address',true)); }, 10, 2);
add_action('add_meta_boxes', function() { add_meta_box('fptlp_lead_details','Thông tin khách đăng ký',function($post) { foreach (['_fptlp_name'=>'Họ tên','_fptlp_phone'=>'Số điện thoại','_fptlp_address'=>'Địa chỉ','_fptlp_package'=>'Gói quan tâm','_fptlp_created'=>'Thời gian','_fptlp_services'=>'Dịch vụ','_fptlp_kind'=>'Loại yêu cầu','_fptlp_region'=>'Khu vực đã chọn','_fptlp_quote'=>'Giá tham khảo khi tiếp nhận','_fptlp_price_basis'=>'Loại giá'] as $key=>$label) echo '<p><strong>'.esc_html($label).':</strong> '.esc_html(get_post_meta($post->ID,$key,true)).'</p>'; },'fptlp_lead','normal','high'); });
