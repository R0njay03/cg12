<?php
// Script to add Facebook link to contact page

echo "<!DOCTYPE html>";
echo "<html><head><title>Add Facebook Link - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 8px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; border-radius: 3px; }
.error { color: red; padding: 8px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; border-radius: 3px; }
.info { color: blue; padding: 8px; background: #f0f8ff; border-left: 3px solid blue; margin: 5px 0; border-radius: 3px; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Add Facebook Link to Contact Page</h1>";
echo "<p>Adding PCG CG-12 Facebook page link</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// Read the contact.html file
$contact_file = 'contact.html';

if (!file_exists($contact_file)) {
    echo "<div class='error'>✗ Contact file not found: $contact_file</div>";
    echo "</div></body></html>";
    exit();
}

echo "<div class='info'>Reading contact.html file...</div>";

$content = file_get_contents($contact_file);

// Add Facebook contact item in the contact information section
$facebook_contact_item = '        
        <div class="contact-item">
          <div class="contact-icon">
            <i class="fab fa-facebook"></i>
          </div>
          <div class="contact-details">
            <h4>Facebook Page</h4>
            <p><a href="https://www.facebook.com/share/19oLPrhbBx/" target="_blank">PCG CG-12 Official Facebook</a></p>
            <p>Follow us for updates and announcements</p>
          </div>
        </div>';

// Find the position to insert the Facebook contact item (after the Department Head item)
$department_head_end = '</div>
        </div>
      </div>
      
      <!-- Contact Form -->';

$facebook_contact_replacement = '</div>
        </div>
        
' . $facebook_contact_item . '
      </div>
      
      <!-- Contact Form -->';

// Replace the Department Head section end with the new section including Facebook
$content = str_replace($department_head_end, $facebook_contact_replacement, $content);

// Update the footer Facebook link
$old_facebook_link = '<a href="#"><i class="fab fa-facebook"></i></a>';
$new_facebook_link = '<a href="https://www.facebook.com/share/19oLPrhbBx/" target="_blank" title="PCG CG-12 Facebook Page"><i class="fab fa-facebook"></i></a>';

$content = str_replace($old_facebook_link, $new_facebook_link, $content);

// Also update the index-improved.html file if it exists
$index_file = 'index-improved.html';
if (file_exists($index_file)) {
    echo "<div class='info'>Updating Facebook link in index-improved.html...</div>";
    
    $index_content = file_get_contents($index_file);
    $index_content = str_replace($old_facebook_link, $new_facebook_link, $index_content);
    
    if (file_put_contents($index_file, $index_content)) {
        echo "<div class='success'>✓ Updated Facebook link in index-improved.html</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Failed to update index-improved.html</div>";
        $error_count++;
    }
}

// Save the updated contact.html file
if (file_put_contents($contact_file, $content)) {
    echo "<div class='success'>✓ Successfully added Facebook link to contact page</div>";
    $success_count++;
    
    // Verify the changes
    if (strpos($content, 'https://www.facebook.com/share/19oLPrhbBx/') !== false) {
        echo "<div class='success'>✓ Facebook URL verified in contact page</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Facebook URL not found after update</div>";
        $error_count++;
    }
    
} else {
    echo "<div class='error'>✗ Failed to save updated contact.html file</div>";
    $error_count++;
}

// Add some additional styling for the Facebook contact item
$facebook_css = '
    /* Facebook contact item styling */
    .contact-item .contact-icon i.fab.fa-facebook {
        background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%);
    }
    
    .contact-item:has(.fab.fa-facebook):hover {
        border-left: 4px solid #1877f2;
    }
    
    .contact-details a[href*="facebook"] {
        color: #1877f2;
        font-weight: 600;
    }
    
    .contact-details a[href*="facebook"]:hover {
        color: #166fe5;
        text-decoration: underline;
    }
';

// Add the CSS to the contact file
$css_insertion_point = '</style>';
$content = str_replace($css_insertion_point, $facebook_css . $css_insertion_point, $content);

if (file_put_contents($contact_file, $content)) {
    echo "<div class='success'>✓ Added Facebook-specific styling</div>";
    $success_count++;
} else {
    echo "<div class='error'>✗ Failed to add Facebook styling</div>";
    $error_count++;
}

// Summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Facebook Link Addition Complete!</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Facebook link added successfully!</strong><br>";
    echo "• Added Facebook contact item in the contact information section<br>";
    echo "• Updated footer Facebook link with the provided URL<br>";
    echo "• Added Facebook-specific styling and hover effects<br>";
    echo "• Facebook URL: <a href='https://www.facebook.com/share/19oLPrhbBx/' target='_blank'>https://www.facebook.com/share/19oLPrhbBx/</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Addition completed with some errors.</strong><br>";
    echo "Please review the errors above and check the contact page manually.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='contact.html' class='btn'><i class='fab fa-facebook'></i> View Contact Page</a>";
echo "<a href='index-improved.html' class='btn'><i class='fas fa-home'></i> Home Page</a>";
echo "<a href='https://www.facebook.com/share/19oLPrhbBx/' target='_blank' class='btn'><i class='fab fa-facebook'></i> Visit Facebook Page</a>";
echo "</div>";

echo "</div></body></html>";
?>