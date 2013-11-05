<?php
/** 
*
* contact [English]
*
* @package	language
* @version	1.0.9 2009-12-25
* @copyright(c) 2009 RMcGirr83
* @copyright (c) 2007 eviL3
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
 
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(

	// teh form
	
	'CONTACT_BOT_ERROR'						=> 'No se puede usar el formulario de contacto porque hay un error en la configuración. Se ha enviado un e-mail al fundador del foro.',
	'CONTACT_BOT_NONE'						=> 'El usuario %1$s intentó usar el Contact Admin Modification a %2$s para mandar un %3$s, pero no hay Administradores que permitan %3$ss de usuarios.' . "\n\n" . 'Por favor ingresá el Contact Bot Configuration en el Panel de Administrador del foro %4$s y seleccioná la opción “Board Founder”' . "\n\n" . 'La modificación fue desactivada',
	'CONTACT_BOT_SUBJECT'					=> 'Error en el Contact Board Administration Modification',
	'CONTACT_BOT_USER_MESSAGE'				=> 'El usuario %1$s intentó usar el Contact Admin modification en %2$s, pero el usuario seleccionado en la configuración es incorrecta.' . "\n\n" . 'Por favor visitá el foro %3$s y elegí otro usuario en el para el Contact Board Administration.' . "\n\n" . 'La modificación fue desactivada',
	'CONTACT_BOT_FORUM_MESSAGE'				=> 'El usuario %1$s tried to use the Contact Admin modification at %2$s, but the forum selected in the configuration is incorrect.' . "\n\n" . 'Please visit the forum %3$s and choose a different forum in the ACP for the Contact Board Administration.' . "\n\n" . 'La modificación fue desactivada',
	'CONTACT_CONFIRM'						=> 'Confirmado',
	'CONTACT_INSTALLED'						=> 'La modificación de “Contact Board Administration” ha sido instalada exitosamente.',

	'CONTACT_DISABLED'			=> 'No podés usar el formulario de contacto porque está desactivado.',
	'CONTACT_MAIL_DISABLED'		=> 'Hubo un error en la configuración del Contact Board Administration Mod.<br />El mod está configurado para mandar un mail pero el envío de e-mails no está activado en la configuración del foro.  Por favor notificá al administrador del foro: <a href="mailto:%1$s">%1$s</a>', 
	'CONTACT_MSG_SENT'			=> 'Tu mensaje ha sido mandado exitosamente',
	'CONTACT_MSG_BODY_EXPLAIN'	=> '<br /><span>Por favor, usá el formulario de contacto <strong><em>sólo</em></strong> si no hay otra forma de contactarnos.<br /><br /><span style="text-align:center;"><strong>Tu dirección IP está siendo grabada y cualquier intento de abuso será castigado.</strong></span></span>',
	'CONTACT_NO_NAME'			=> 'No ingresaste un nombre.',
	'CONTACT_NO_EMAIL'			=> 'No ingresaste una dirección de e-mail.',
	'CONTACT_NO_MSG'			=> 'No ingresaste un mensaje.',
	'CONTACT_NO_SUBJ'			=> 'No ingresaste un asunto.',
	'CONTACT_NO_REASON'			=> 'No ingresaste una razón válida.',
	'CONTACT_OUTDATED'			=> 'La base de datos para la página de contacto no ha sido actualizada todavía. Por favor esperá a que un Administrador la actualice.',
	'CONTACT_REASON'			=> 'Razón',
	'CONTACT_TEMPLATE'			=> '<strong>Nombre:</strong> %1$s' . "\n" . '<strong>Dirección de e-mail:</strong> %2$s' . "\n" . '<strong>IP:</strong> %3$s' . "\n" . '<strong>Fecha:</strong> %4$s' . "\n" . '<strong>Razón:</strong> %5$s' . "\n" . '<strong>Asunto:</strong> %6$s' . "\n\n" . '<strong>Ha ingresado el siguiente mensaje en el formulario de contacto:</strong>' . "\n" . '%7$s',
	'CONTACT_TEMPLATE_NO_REASON'	=> '<strong>Nombre:</strong> %1$s' . "\n" . '<strong>Dirección de e-mail:</strong> %2$s' . "\n" . '<strong>IP:</strong> %3$s' . "\n" . '<strong>Fecha:</strong> %4$s' . "\n" . '<strong>Asunto:</strong> %5$s' . "\n\n" . '<strong>Ha ingresado el siguiente mensaje en el formulario de contacto:</strong>' . "\n" . '%6$s',
	'CONTACT_TITLE'				=> 'Contact Board Administration',
	'CONTACT_TOO_MANY'			=> 'Te has excedido en el número máximo de intentos de confirmación de contacto en esta sesión. Por favor intentá de nuevo en unos momentos.',
	'CONTACT_UNINSTALLED'		=> 'La modificación de “contact board administration” ha sido exitosamente desinstalada.',
	'CONTACT_UPDATED'			=> 'La modificación de “contact board administration” ha sido actualizada a la versión %s exitosamente.',
	
	'CONTACT_YOUR_NAME'				=> 'Tu nombre',
	'CONTACT_YOUR_NAME_EXPLAIN'		=> 'Por favor ingresá un nombre, para que el mensaje tenga una identidad.',
	'CONTACT_YOUR_EMAIL'			=> 'Tu dirección de e-mail',
	'CONTACT_YOUR_EMAIL_EXPLAIN'	=> 'Por favor ingresá una dirección de e-mail válida, para que podamos contactarte.',
	'CONTACT_YOUR_EMAIL_CONFIRM'	=> 'Reingresá tu dirección de e-mail',
	'CONTACT_YOUR_EMAIL_CONFIRM_EXPLAIN'	=> 'Por favor reingresá tu dirección de e-mail.',	

	'RETURN_CONTACT'				=> '%sVolver a la página de contacto%s',
	'URL_UNAUTHED'		=> 'No podés postear urls, por favor removelas o renombralas:<br /><em>%1$s</em>',
));

?>