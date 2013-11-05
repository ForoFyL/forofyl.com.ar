/**
*
* @package mChat JavaScript Code mini
* @version 1.3.2 of 10.09.2009
* @copyright (c) By Richard McGirr (RMcGirr83) http://rmcgirr83.org
* @copyright (c) By Shapoval Andrey Vladimirovich (AllCity) ~ http://allcity.net.ru/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

// no Conflict jQuery with Other Libraries (Including jQuery before Other Libraries! - http://docs.jquery.com/Using_jQuery_with_Other_Libraries#Including_jQuery_before_Other_Libraries)
var $jQ = jQuery;

if($jQ.cookie('mChatNoSound') == 'yes')
{
  // Cookie check
  $jQ('#mChatUseSound').attr('checked', false);
}

// mChat AJAX function
var mChat = {
  // Sound function
  sound: function(file)
  {
    if($jQ.cookie('mChatNoSound') == 'yes')
    {
      // Stop
      return;
    }
    if($jQ.browser.msie)
    {
      // For IE ;)
      document.getElementById('mChatSound').innerHTML = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" height="0" width="0" type="application/x-shockwave-flash"><param name="movie" value="' + file + '"></object>';
    }
      else
    {
      // For FireFox, Opera, Safari...
      $jQ('#mChatSound').html('<embed src="' + file + '" width="0" height="0" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>');
    }
  },

  // Toggle function
  toggle: function(id)
  {
    // Toggle :)
    $jQ('#mChat' + id).slideToggle('normal', function(){
        // Cookie set
        if($jQ('#mChat' + id).is(':visible'))
        {
          $jQ.cookie('mChatShow' + id, 'yes');
        }
        // Cookie delete
        if($jQ('#mChat' + id).is(':hidden'))
        {
          $jQ.cookie('mChatShow' + id, null);
        }
    });

  },

  // Send function
  add: function()
  {
    // If message input empty stop Send function
    if($jQ('#mChatMessage').val() == '')
    {
      // Error alert
      alert(mChatNoMessageInput);
      // Stop!
      return;
    }
      $jQ.ajax({
        url: mChatFile,
        timeout: 10000,
        type: 'POST',
        async: false,
        data: $jQ('#mChatForm').serialize(),
        dataType: 'text',
        success: function()
        {
          // Empty message input
          $jQ('#mChatMessage').val('');
          // Run refresh function
          mChat.refresh();
        },
        error: function(XMLHttpRequest)
        {
          if(XMLHttpRequest.status == 400)
          {
            // Flood alert
            alert(mChatFlood);
          }
            else if(XMLHttpRequest.status == 403)
          {
            // No access alert
            alert(mChatNoAccess);
          }
            else if(XMLHttpRequest.status == 501)
          {
            // No message alert
            alert(mChatNoMessageInput);
          }
        },
        beforeSend: function()
        {
          // Refresh stop
          window.clearInterval(interval);
        },
        complete: function()
        {
          // Start refresh
          interval = setInterval(function(){mChat.refresh()}, mChatRefresh);
        }
      });
  },

  // Edit function
  edit: function(id)
  {
    var message = $jQ('#edit' + id).val();
    var data = prompt(mChatEditInfo, message);
      if(data)
      {
        // AJAX request
        $jQ.ajax({
          url: mChatFile,
          timeout: 10000,
          type: 'POST',
          async: true,
          data: {mode: 'edit', message_id: id, message: data},
          dataType: 'text',
          success: function(html)
          {
            // Replace old edited message to new with animation
            $jQ('#mess' + id).fadeOut('slow', function(){
              $jQ(this).replaceWith(html);
              // Animation
              $jQ('#mess' + id).css('display', 'none').fadeIn('slow');
            });
          },
          error: function(XMLHttpRequest)
          {
            if(XMLHttpRequest.status == 403)
            {
              // No access alert
              alert(mChatNoAccess);
            }
              else if(XMLHttpRequest.status == 501)
            {
              // No message alert
              alert(mChatNoMessageInput);
            }
          },
          beforeSend: function()
          {
            // Refresh stop
            window.clearInterval(interval);
          },
          complete: function()
          {
            // Start refresh
            interval = setInterval(function(){mChat.refresh()}, mChatRefresh);
          }
        });
      }
  },

  // Delete function
  del: function(id)
  {
    // Confirm to delete
    if(confirm(mChatDelConfirm))
    {
      // AJAX request
      $jQ.ajax({
        url: mChatFile,
        timeout: 10000,
        type: 'POST',
        async: true,
        data: {mode: 'delete', message_id: id},
        success: function()
        {
          // Animation ;)
          $jQ('#mess' + id).fadeOut('slow', function(){
            // Remove message
            $jQ(this).remove();
          });
          // Sound
          mChat.sound(mChatForumRoot + 'mchat/del.swf');
        },
        error: function()
        {
          // Not Extended alert
          alert(mChatNoAccess);
        },
        beforeSend: function()
        {
          // Refresh stop
          window.clearInterval(interval);
        },
        complete: function()
        {
          // Start refresh
          interval = setInterval(function(){mChat.refresh()}, mChatRefresh);
        }
      });
    }
  },

  // Refresh function
  refresh: function()
  {
  
    // If archive page
    if(mChatArchiveMode)
    {
        // If no message
        if($jQ('#mChatData').find('div:first').not('#mChatArchiveNoMessage').not('#mChatNoMessage').attr('id') == undefined)
        {
          // Show div text
          $jQ('#mChatArchiveNoMessage').show('slow');
        }
      // Stop
      return;
    }
      // Default id
      var mess_id = 0;
        // Fix if all message deleted
        if($jQ('#mChatData').find('div:first').not('#mChatNoMessage').attr('id') != undefined)
        {
          mess_id = $jQ('#mChatData').find('div:first').attr('id').replace('mess', '');
        }
          // AJAX request
          $jQ.ajax({
            url: mChatFile,
            timeout: 10000,
            type: 'POST',
            async: true,
            data: {mode: 'read', message_last_id: mess_id},
            dataType: 'html',
            beforeSend: function()
            {
              // Indicator status
              $jQ('#mChatLoadIMG').show();
              $jQ('#mChatOkIMG').hide();
              $jQ('#mChatErrorIMG').hide();
            },
            success: function(html)
            {
              // If not empty run its part
              if(html != '')
              {
                // Prepend data to mChat
                $jQ('#mChatData').prepend(html).find('div:first').not('#mChatNoMessage').css('display', 'none');
                // Animation ;)
                $jQ('#mChatData div:first').not('#mChatNoMessage').fadeIn('slow');
                // Sound
                mChat.sound(mChatForumRoot + 'mchat/add.swf');
                // Hide no message if exist
                $jQ('#mChatNoMessage').hide();
              }
              // setTimeout for IE fix
              setTimeout(function(){
                // Indicator status
                $jQ('#mChatLoadIMG').hide();
                $jQ('#mChatOkIMG').show();
                $jQ('#mChatErrorIMG').hide();
              }, 1000);
            },
            error: function()
            {
              // Indicator status
              $jQ('#mChatLoadIMG').hide();
              $jQ('#mChatOkIMG').hide();
              $jQ('#mChatErrorIMG').show();
              // Sound
              mChat.sound(mChatForumRoot + 'mchat/error.swf');
            },
            complete: function()
            {
              // Set no message
              if($jQ('#mChatData').find('div:first').not('#mChatNoMessage').attr('id') == undefined)
              {
                // Show div text
                $jQ('#mChatNoMessage').show('slow');
              }
            }
          });
  },
  
  // whois chatting Refresh function
  stats: function()
  {
    // If whois
    if(!mChatCustomWhois)
    {
      // Stop
      return;
    }  
		// AJAX request
        $jQ.ajax({
          url: mChatFile,
          timeout: 10000,
          type: 'POST',
          async: false,
          data: {mode: 'stats'},
          dataType: 'html',
            beforeSend: function()
            {
              // Indicator status
              $jQ('#mChatRefreshN').show();
              $jQ('#mChatRefresh').hide();
			  // Refresh stop
			  window.clearInterval(statsinterval);			  
            },		  
            success: function(stats)
            {
 		
				// Replace old stats with animation
				$jQ('#mChatStats').fadeOut('slow', function(){
                // overwrite data to stats
				$jQ('#mChatStats').html(stats);
                // Animation ;)
                $jQ('#mChatStats').css('display', 'none').fadeIn('slow');
				});
              // setTimeout for IE fix
              setTimeout(function(){
                // Indicator status
                $jQ('#mChatRefreshN').hide();
                $jQ('#mChatRefresh').show();
              }, 1000);				
            },
            error: function()
            {
              // Sound
              mChat.sound(mChatForumRoot + 'mchat/error.swf');
            },			
			complete: function()
			{
				// Start refresh
				statsinterval = setInterval(function(){mChat.stats()}, mChatWhoisRefresh);
			}			
          });
  }  
};

// ReFresh mChat
var interval = setInterval(function(){mChat.refresh()}, mChatRefresh);

// ReFresh Whois stats
var statsinterval = setInterval(function(){mChat.stats()}, mChatWhoisRefresh);
// Toggle Smiles cookie function
if($jQ.cookie('mChatShowSmiles') == 'yes')
{
  $jQ('#mChatSmiles:hidden').slideToggle('slow');
}

// Toggle BBcodes cookie function
if($jQ.cookie('mChatShowBBCodes') == 'yes')
{
  $jQ('#mChatBBCodes:hidden').slideToggle('slow');
}

// Sound cookie function
$jQ('#mChatUseSound').change(function(){
  // Check if input checked
  if($jQ(this).is(':checked'))
  {
    // Cookie delete
    $jQ.cookie('mChatNoSound', null);
  }
    else 
  {
    // Cookie set
    $jQ.cookie('mChatNoSound', 'yes');
  }
});