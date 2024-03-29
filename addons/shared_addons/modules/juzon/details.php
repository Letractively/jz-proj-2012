<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Templates Details.php
 *
 * Create and store dynamic email templates in the database
 *
 * @package		PyroCMS
 * @subpackage	Templates Module
 * @category	Module
 * @author		PyroCMS Dev Team
 */
class Module_Juzon extends Module {

	public $version = '0.1';

	public function info()
	{
		return array(
			'name' => array(
				'sl' => 'Juzon',
				'en' => 'Juzon',
                'fr' => 'Juzon',
				'nl' => 'Juzon',
				'es' => 'Juzon',
				'ar' => 'Juzon',
				'br' => 'Juzon',
				'el' => 'Juzon',
				'he' => 'Juzon',
				'lt' => 'Juzon',
				'ru' => 'Juzon',
				'da' => 'Juzon',
				'zh' => 'Juzon',
				'id' => 'Juzon'
			),
			'description' => array(
				'sl' => 'Juzon data management',
				'en' => 'Juzon data management',
                'fr' => 'Juzon data management',
				'nl' => 'Juzon data management',
				'es' => 'Juzon data management',
				'ar' => 'Juzon data management',
				'br' => 'Juzon data management',
				'el' => 'Juzon data management',
				'he' => 'Juzon data management',
				'lt' => 'Juzon data management',
				'ru' => 'Juzon data management',
				'da' => 'Juzon data management',
				'zh' => 'Juzon data management',
				'id' => 'Juzon data management'
			),
			'frontend' => FALSE,
			'backend' => TRUE,
			'menu' => 'content',
			'author' => 'Hung Dang',
			
			'shortcuts' => array(
				/*
				array(
				    'name' => 'templates.create_title',
				    'uri' => 'admin/templates/create',
				    'class' => 'add'
				),
				*/
		    ),
		);
	}

	public function install()
	{
		/*
		$this->dbforge->drop_table('email_templates');
		
		$email_templates = "
            CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('email_templates') . " (
            `id` int(11) NOT NULL AUTO_INCREMENT,
			`slug` varchar(100) NOT NULL,
			`name` varchar(100) NOT NULL,
            `description` varchar(255) NOT NULL,
			`subject` varchar(255) NOT NULL,
			`body` text NOT NULL,
            `lang` varchar(2),
			`is_default` int(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY slug_lang (`slug`, `lang`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Store dynamic email templates';
        ";

		$create = $this->db->query($email_templates);

		$comment_body = '<h3>You have received a comment from {{ name }}</h3>';
		$comment_body .= '<strong>IP Address: {{ sender_ip }}</strong>\n';
		$comment_body .= '<strong>Operating System: {{ sender_os }}\n';
		$comment_body .= '<strong>User Agent: {{ sender_agent }}</strong>\n';
		$comment_body .= '<div>{{ comment }}</div>\n';
		$comment_body .= '<div>View Comment:{{ redirect_url }}</div>';
		$comment_subject = 'You have just received a comment from {{ name }}';

		$comment_template = "
			INSERT INTO " . $this->db->dbprefix('email_templates') . " (`slug`, `name`, `description`, `subject`, `body`, `lang`, `is_default`) VALUES
			('comments', 'Comment Notification', 'Email that is sent to admin when someone creates a comment', '".$comment_subject."', '".$comment_body."', 'en', 1);
		";

		$contact_template = "
			INSERT INTO " . $this->db->dbprefix('email_templates') . " (`slug`, `name`, `description`, `subject`, `body`, `lang`, `is_default`) VALUES ('contact', 'Contact Notification', 'Template for the contact form', '{{ settings:site_name }} :: {{ subject }}', 'This message was sent via the contact form on with the following details:
				<hr />
				IP Address: {{ sender_ip }}
				OS {{ sender_os }}
				Agent {{ sender_agent }}
				<hr />
				{{ message }}

				{{ name }},
				{{ email }}', 'en', '1');
		";
		
		$registered_template = "
			INSERT INTO " . $this->db->dbprefix('email_templates') . " (`slug`, `name`, `description`, `subject`, `body`, `lang`, `is_default`) VALUES ('registered', 'New User Registered', 'The email sent to the site contact e-mail when a new user registers', '{{ settings:site_name }} :: You have just received a registration from {{ name}', '<h3>You have received a registration from {{ name}</h3><strong>IP Address: {{ sender_ip }}</strong>
				<strong>Operating System: {{ sender_os }}
				<strong>User Agent: {{ sender_agent }}</strong>', 'en', '1');
		";
		
		$newsletters_opt_in_template = "
			INSERT INTO " . $this->db->dbprefix('email_templates') . " (`slug`, `name`, `description`, `subject`, `body`, `lang`, `is_default`) VALUES ('newsletters_opt_in', 'Newsletters Opt In', 'Template for the email that\'s sent when a user subscribes.', '{{ settings:site_name }} :: Newsletter Activation',
			'<h3>You have recently subscribed to the newsletter at {{ settings:site_name }}</h3>
			<p><strong>To verify that you wish to have your email address added to our list you must click the activation link below.</strong><strong> </strong></p>
			<p><strong>If you did not sign up at our website please disregard this email. No further action is necessary.</strong></p>
			<p><span>Complete signup: <a href=\"{{ newsletter_activation }}\">{{ newsletter_activation }}</a></span></p>
			', 'en', '1');
		";
		
		$activation_template = array(
			'slug'				=> 'activation',
			'name'				=> 'Activation Email',
			'description' 		=> 'The email which contains the activation code that is sent to a new user',
			'subject'			=> '{{ settings:site_name }} - Account Activation',
			'body'				=> '<p>Hello {{ user:first_name }},</p>
									<p>Thank you for registering at {{ settings:site_name }}. Before we can activate your account, please complete the registration process by clicking on the following link:</p>
									<p><a href="{{ url:site }}users/activate/{{ user:id }}/{{ activation_code }}">{{ url:site }}users/activate/{{ user:id }}/{{ activation_code }}</a></p>
									<p>&nbsp;</p>
									<p>In case your email program does not recognize the above link as, please direct your browser to the following URL and enter the activation code:</p>
									<p><a href="{{ url:site }}users/activate">{{ url:site }}users/activate</a></p>
									<p><strong>Activation Code:</strong> {{ activation_code }}</p>',
			'lang'				=> 'en',
			'is_default'		=> 1
		);
		
		$forgotten_password_template	= array(
			'slug'				=> 'forgotten_password',
			'name'				=> 'Forgotten Password Email',
			'description' 		=> 'The email that is sent containing a password reset code',
			'subject'			=> '{{ settings:site_name }} - Forgotten Password',
			'body'				=> '<p>Hello {{ user:first_name }},</p>
									<p>It seems you have requested a password reset. Please click this link to complete the reset: <a href="{{ url:site }}users/reset_pass/{{ user:forgotten_password_code }}">{{ url:site }}users/reset_pass/{{ user:forgotten_password_code }}</a></p>
									<p>If you did not request a password reset please disregard this message. No further action is necessary.</p>',
			'lang'				=> 'en',
			'is_default'		=> 1
		);
		
		$new_password		= array(
			'slug'				=> 'new_password',
			'name'				=> 'New Password Email',
			'description' 		=> 'After a password is reset this email is sent containing the new password',
			'subject'			=> '{{ settings:site_name }} - New Password',
			'body'				=> '<p>Hello {{ user:first_name }},</p>
									<p>Your new password is: {{ new_password }}</p>
									<p>After logging in you may change your password by visiting <a href="{{ url:site }}edit-profile">{{ url:site }}edit-profile</a></p>',
			'lang'				=> 'en',
			'is_default'		=> 1
		);

			$this->db->query($comment_template); //sent when a user posts a comment to something
			$this->db->query($contact_template); //sent when a user uses the contact form
			$this->db->query($registered_template); // sent to the site contact email when a new user registers
			$this->db->query($newsletters_opt_in_template); //required by the newsletters module.
			$this->db->insert('email_templates', $activation_template); // when user registers this is used to send his activation code
			$this->db->insert('email_templates', $forgotten_password_template); // sent when user requests a password reset
			$this->db->insert('email_templates', $new_password); // this is used to send the new password
		*/	
			return TRUE;
	}

	public function uninstall()
	{
		//it's a core module, lets keep it around
		return FALSE;
	}

	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}

	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "No documentation has been added for this module.<br/>Contact the module developer for assistance.";
	}
}

/* End of file details.php */
