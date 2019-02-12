<?php
/**
 * Initialization functions for WPLMS LEARNDASH MIGRATION
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     1.0
 */

if ( !defined( 'ABSPATH' ) ) exit;

class WPLMS_LMS_INIT{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_LMS_INIT();

        return self::$instance;
    }

    private function __construct(){

    	//if ( is_defined('IAMD_BASE_URL')) {
			add_action( 'admin_notices',array($this,'migration_notice' ));
			add_action('wp_ajax_migration_lms_courses',array($this,'migration_lms_courses'));

			add_action('wp_ajax_migration_lms_course_to_wplms',array($this,'migration_lms_course_to_wplms'));
            add_action('wp_ajax_revert_migrated_courses',array($this,'revert_migrated_courses'));
            add_action('wp_ajax_dismiss_message',array($this,'dismiss_message'));
		//}

            add_shortcode('iframe_loader',function( $atts, $content = null ) {
                extract(shortcode_atts(array(
                'height'   => '',
                'width'=>'',
                'src'=>''
                ), $atts));

                return do_shortcode('[iframe height="'.$height.'"]'.$src.'[/iframe]');
            });
    }

    function migration_notice(){

    	$this->migration_status = get_option('wplms_lms_migration');
        $this->revert_status = get_option('wplms_lms_migration_reverted');

        if(!empty($this->migration_status && empty($this->revert_status))){
            ?>
            <div id="migration_lms_courses_revert" class="update-nag notice ">
               <p id="revert_message"><?php printf( __('LMS Courses migrated to WPLMS: Want to revert changes %s Revert Changes Now %s Otherwise dismiss this notice.', 'wplms-lms' ),'<a id="begin_revert_migration" class="button primary">','</a><a id="dismiss_message" href=""><i class="fa fa-times-circle-o"></i>Dismiss</a>'); ?>
               </p>
            </div>
            <style>
                #migration_learndash_courses_revert{width:97%;} 
                #dismiss_message {float:right;padding:5px 10px 10px 10px;color:#e00000;}
                #dismiss_message i {padding-right:3px;}
            </style>
            <?php wp_nonce_field('security','security'); ?>
            <script>
                jQuery(document).ready(function($){
                    $('#begin_revert_migration').on('click',function(){
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: { action: 'revert_migrated_courses', 
                                      security: $('#security').val(),
                                    },
                            cache: false,
                            success: function () {
                                $('#migration_learndash_courses_revert').removeClass('update-nag');
                                $('#migration_learndash_courses_revert').addClass('updated');
                                $('#migration_learndash_courses_revert').html('<p id="revert_message">'+'<?php _e('WPLMS - LEARNDASH MIGRATION : Migrated courses Reverted !', 'wplms-lms' ); ?>'+'</p>');
                            }
                        });
                    });
                    $('#dismiss_message').on('click',function(){
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: { action: 'dismiss_message', 
                                      security: $('#security').val(),
                                    },
                            cache: false,
                            success: function () {
                                
                            }
                        });
                    });
                });
            </script>
            <?php
            return;
        }        
        
        $check = 1;
        if(!function_exists('WC') && empty($this->migration_status) ){
            $check = 0;
            ?>
            <div class="welcome-panel" id="welcome_lms_panel" style="padding-bottom:20px;width:96%">
                <img src="https://wplms.io/demos/demo1/wp-content/uploads/2018/02/logo_black.png" style="float:left;width:120px;margin: 0 15px 15px 0;" />
                <h1><?php echo __('Please note the following before starting migration:','wplms-lms'); ?></h1>
                <ol style="clear:both">
                    <li><?php echo __('Woocommerce must be activated if using paid courses.','wplms-lms'); ?></li>
                    <li><?php echo __('WPLMS vibe custom types plugin must be activated.','wplms-lms'); ?></li>
                </ol>
                <p><?php echo __('If all the above plugins are activated then please click on the button below to proceed to migration proccess','wplms-lms'); ?></p>
                <form method="POST">
                    <input name="click" type="submit" value="<?php echo __('Click Here','wplms-lms'); ?>" class="button">
                </form>
            </div>
            <?php
        }
        if(isset($_POST['click'])){
            $check = 1;
            ?> <style> #welcome_ld_panel{display:none;} </style> <?php
        }

    	if(empty($this->migration_status) && $check){
    		?>
    		<div id="migration_lms_courses" class="error notice ">
		       <p id="ldm_message"><?php printf( __('Migrate LMS courses to WPLMS %s Begin Migration Now %s', 'wplms-lms' ),'<a id="begin_wplms_lms_migration" class="button primary">','</a>'); ?>
		       	
		       </p>
		   <?php wp_nonce_field('security','security'); ?>
		        <style>.wplms_lms_progress .bar{-webkit-transition: width 0.5s ease-in-out;
    -moz-transition: width 1s ease-in-out;-o-transition: width 1s ease-in-out;transition: width 1s ease-in-out;}</style>
		        <script>
		        	jQuery(document).ready(function($){
		        		$('#begin_wplms_lms_migration').on('click',function(){
			        		$.ajax({
			                    type: "POST",
			                    dataType: 'json',
			                    url: ajaxurl,
			                    data: { action: 'migration_lms_courses', 
			                              security: $('#security').val(),
			                            },
			                    cache: false,
			                    success: function (json) {

			                    	$('#migration_lms_courses').append('<div class="wplms_ld_progress" style="width:100%;margin-bottom:20px;height:10px;background:#fafafa;border-radius:10px;overflow:hidden;"><div class="bar" style="padding:0 1px;background:#37cc0f;height:100%;width:0;"></div></div>');

			                    	var x = 0;
			                    	var width = 100*1/json.length;
			                    	var number = width;
									var loopArray = function(arr) {
									    lms_ajaxcall(arr[x],function(){
									        x++;
									        if(x < arr.length) {
									         	loopArray(arr);   
									        }
									    }); 
									}
									
									// start 'loop'
									loopArray(json);

									function lms_ajaxcall(obj,callback) {
										
				                    	$.ajax({
				                    		type: "POST",
						                    dataType: 'json',
						                    url: ajaxurl,
						                    data: {
						                    	action:'migration_lms_course_to_wplms', 
						                        security: $('#security').val(),
						                        id:obj.id,
						                    },
						                    cache: false,
						                    success: function (html) {
						                    	number = number + width;
						                    	$('.wplms_ld_progress .bar').css('width',number+'%');
						                    	if(number >= 100){
                                                    $('#migration_lms_courses').removeClass('error');
                                                    $('#migration_lms_courses').addClass('updated');
                                                    $('#ldm_message').html('<strong>'+x+' '+'<?php _e('Courses successfully migrated from LMS to WPLMS','wplms-lms'); ?>'+'</strong>');
										        }
						                    }
				                    	});
									    // do callback when ready
									    callback();
									}
			                    }
			                });
		        		});
		        	});
		        </script>
		    </div>
		    <?php
    	}
    }

    function migration_lms_courses(){
    	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
         	_e('Security check Failed. Contact Administrator.','wplms-lms');
         	die();
      	}

      	global $wpdb;
		$courses = $wpdb->get_results("SELECT id,post_title FROM {$wpdb->posts} where post_type='dt_courses'");
		$json=array();
		foreach($courses as $course){
			$json[]=array('id'=>$course->id,'title'=>$course->post_title);
		}
		update_option('wplms_lms_migration',1);
		delete_option('wplms_lms_migration_reverted');
		$this->migrate_posts();

		print_r(json_encode($json));
		die();
    }

    function revert_migrated_courses(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
            _e('Security check Failed. Contact Administrator.','wplms-lms');
            die();
        }
        delete_option('wplms_lms_migration');
        update_option('wplms_lms_migration_reverted',1);
        $this->revert_migrated_posts();
        die();
    }

    function dismiss_message(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
            _e('Security check Failed. Contact Administrator.','wplms-lms');
            die();
        }
        update_option('wplms_lms_migration_reverted',1);
        die();
    }

    function migrate_posts(){
        global $wpdb;

    	$wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'course' WHERE post_type = 'dt_courses'");
    	$wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'unit' WHERE post_type = 'dt_lessons' OR post_type = 'sfwd-topic'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'quiz' WHERE post_type = 'dt_quizes'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'question' WHERE post_type = 'dt_questions'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'wplms-assignment' WHERE post_type = 'dt_assignments'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'certificate' WHERE post_type = 'dt_certificates'");

    }

    function revert_migrated_posts(){
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'dt_courses' WHERE post_type = 'course'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'dt_lessons' WHERE post_type = 'unit'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'dt_quizes' WHERE post_type = 'quiz'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'dt_questions' WHERE post_type = 'question'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'dt_assignments' WHERE post_type = 'wplms-assignment'");
         $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'dt_certificates' WHERE post_type = 'certificate'");
       

    }

    function migration_lms_course_to_wplms(){
    	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
         	_e('Security check Failed. Contact Administrator.','wplms-lms');
         	die();
      	}

    	global $wpdb;
		$this->migrate_course_settings($_POST['id']);
		$this->course_pricing($_POST['id']);
    }

    function migrate_course_settings($course_id){


        
        $check_featured = get_post_meta($course_id,'featured-course',true);
        if($check_featured == 'true'){
            update_post_meta($course_id,'featured',true);
        }

    	$postmetas =array(
            'featured-course'=>array('key'=>'featured','value_map'=>array(
                'true'=>1)),
            'certificate-percentage'=>array('key'=>'vibe_course_passing_percentage'),
            'enable-certificate'=>array('key'=>'vibe_course_certificate','value_map'=>array(
                'true'=>'S')),
            'certificate-template'=>array('key'=>'vibe_certificate_template'),
            'dt-course-product-id'=>array('key'=>'vibe_product'),
            'mr_rating_results_star_rating'=>array('key'=>'featured','value_map'=>array(
                'true'=>1)),
            'mr_rating_results_count_entries'=>array('key'=>'featured','value_map'=>array(
                'true'=>1)),
            'course-video'=>array('key'=>'post_video'),
            'badge-percentage'=>array('key'=>'vibe_course_badge_percentage'),
            'badge-image'=>array('key'=>'vibe_course_badge')
        );
        
        foreach($postmetas as $key => $map){
            $stored_value = get_post_meta($course_id,$key,true);
            if(!empty($map['value_map'])){
                if(!empty($map['value_map'][$stored_value])){
                    update_post_meta($course_id,$map['key'],$stored_value);    
                }
            }else{
                update_post_meta($course_id,$map['key'],$stored_value);
            }
        }
        
    	
		$this->build_curriculum($_POST['id']);
    }

    function build_curriculum($course_id){
    	global $wpdb;

    	$lessons = $wpdb->get_results("
            SELECT m.post_id as id,p.post_type as type,p.post_title as title 
            FROM {$wpdb->postmeta} as m 
            LEFT JOIN {$wpdb->posts} as p 
            ON p.id = m.post_id 
            WHERE m.meta_value = $course_id 
            AND m.meta_key = 'dt_lesson_course' 
            ORDER BY p.menu_order asc"
        );
    	if(!empty($lessons)){
    		foreach($lessons as $unit){
    			switch($unit->type){
    				case 'unit':
                    case 'dt_lessons':

                        $curriculum[] = $unit->id;
                        $this->migrate_unit_settings($unit->id);
    					$quiz_id = get_post_meta($unit->id,'lesson-quiz',true);
                        if(!empty($quiz_id)){
                            $this->migrate_quiz_settings($quiz_id);
                            $curriculum[] = $quiz_id;
                        }
    				break;
    			}
    		}

    	}
    	update_post_meta($course_id,'vibe_course_curriculum',$curriculum);
    }
    
    function migrate_unit_settings($unit_id){
       
    }

    function migrate_quiz_settings($quiz_id){
        global $wpdb;
        
        $metas = array(
            'quiz-subtitle'=>'vibe_subtitle',
            'quiz-duration'=>'vibe_duration',
            'quiz-retakes'=>'vibe_quiz_retakes',
            'quiz-postmsg'=>'vibe_quiz_message',
            'quiz-randomize-questions'=>'vibe_quiz_random'
        );

        foreach($metas as $key => $mapped_key){
            $v = get_post_meta($quiz_id,$key,true);
            update_post_meta($quiz_id,$mapped_key,$v);
        }

        $auto = get_post_meta($quiz_id,'quiz-auto-evaluation',true);
        if($auto == true){
            update_post_meta($quiz_id,'vibe_quiz_auto_evaluate','S');
        }

        update_post_meta($quiz_id,'vibe_quiz_duration_parameter',60);
        update_post_meta($quiz_id,'vibe_results_after_quiz_message','S');

        $questions = get_post_meta($quiz_id,'quiz-question',true);
        $marks = get_post_meta($quiz_id,'quiz-question-grade',true);
        
        $quiz_questions = array('ques'=>Array(),'marks'=>array());
        foreach($questions as $key => $question_id){
            $quiz_questions['ques'][$key] = $question_id;
            $quiz_questions['marks'][$key] = $marks[$key];
            $this->migrate_questions($question_id);
        }

        update_post_meta($quiz_id,'vibe_quiz_questions',$quiz_questions);
        
    }

    function migrate_questions($question_id){
        global $wpdb;
        $type = get_post_meta($question_id,'question-type',true);
        switch($type){
            case 'boolean':
                update_post_meta($question_id,'vibe_question_type','truefalse');
            break;
            case 'multiple-choice':

                update_post_meta($question_id,'vibe_question_type','single');

                $options = get_post_meta($question_id,'multichoice-answers',true);
                update_post_meta($question_id,'vibe_question_options',$options);
                $correct = get_post_meta($question_id,'multichoice-correct-answer',true);
                $key = array_search($correct, $options);
                update_post_meta($question_id,'vibe_question_answer',$key);
            break;
            case 'multiple-choice-image':
            update_post_meta($question_id,'vibe_question_type','sigle');
                $options = get_post_meta($question_id,'multichoice-image-answers',true);
                $correct = get_post_meta($question_id,'multichoice-image-correct-answer',true);
                $key = array_search($correct, $options);
                update_post_meta($question_id,'vibe_question_answer',$key);
                foreach($options as $i=>$option){
                    $options[$i]= '<img src="'.$option.'" />';
                }
                update_post_meta($question_id,'vibe_question_options',$options);

            break;
            case 'multicorrect-answers':
            update_post_meta($question_id,'vibe_question_type','multiple');
                $options = get_post_meta($question_id,'multicorrect-answers',true);
                update_post_meta($question_id,'vibe_question_options',$options);
                $mcorrect = get_post_meta($question_id,'multichoice-correct-answer',true);
                $correct_answer =array();
                foreach($mcorrect as $correct){
                    $key = array_search($correct, $options);
                    $correct_answer[]=$key;
                }
                
                update_post_meta($question_id,'vibe_question_answer',implode(',',$correct_answer));
            break;
            case 'gap-fill':
                update_post_meta($question_id,'vibe_question_type','fillblank');
                $correct = get_post_meta($question_id,'gap',true);
                update_post_meta($question_id,'vibe_question_answer',$correct);

                $before = get_post_meta($question_id,'text-before-gap',true);
                $after = get_post_meta($question_id,'text-after-gap',true);
                $content = get_post_field('post_content',$quesiton_id);
                $content .= '<hr>'.$before.' [fillblank] '.$after;
                wp_update_post(array('ID'=>$question_id,'post_content'=>$content));
            break;
            case 'single-line':
                update_post_meta($question_id,'vibe_question_type','smalltext');
                $correct = get_post_meta($question_id,'singleline-answer',true);
                update_post_meta($question_id,'vibe_question_answer',$correct);
            break;
            case 'multi-line':
                update_post_meta($question_id,'vibe_question_type','largetext');
                $correct = get_post_meta($question_id,'multiline-answer',true);
                update_post_meta($question_id,'vibe_question_answer',$correct);
            break;
        }

        $explanation = get_post_meta($question_id,'answer-explanation',true);
        update_post_meta($question_id,'vibe_question_explaination',true);
    }

    function course_pricing($course_id){


        $price = get_post_meta($course_id,'starting-price',true);
        if(!empty($settings['sfwd-courses_course_price'])){

            $post_args=array('post_type' => 'product','post_status'=>'publish','post_title'=>get_the_title($course_id));
            $product_id = wp_insert_post($post_args);
            update_post_meta($product_id,'vibe_subscription','H');

            update_post_meta($product_id,'_price',$price);

            wp_set_object_terms($product_id, 'simple', 'product_type');
            update_post_meta($product_id,'_visibility','visible');
            update_post_meta($product_id,'_virtual','yes');
            update_post_meta($product_id,'_downloadable','yes');
            update_post_meta($product_id,'_sold_individually','yes');
            
            $courses = array($course_id);
            update_post_meta($product_id,'vibe_courses',$courses);
            update_post_meta($course_id,'vibe_product',$product_id);

            $thumbnail_id = get_post_thumbnail_id($course_id);
            if(!empty($thumbnail_id))
                set_post_thumbnail($product_id,$thumbnail_id);
        }
    }
}

WPLMS_LMS_INIT::init();