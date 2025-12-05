<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Browser extends MY_Controller {
    
    // ADD THIS CONSTRUCTOR
    public function __construct() {
        parent::__construct();
        
        // Check authentication for ALL methods
        $this->check_authentication();
    }
    
    private function check_authentication() {
        $login_data = $this->session->userdata('login');
        
        // Check if any user is logged in (admin, agency, recruiter, staff)
        if (empty($login_data)) {
            // For AJAX requests
            if ($this->input->is_ajax_request()) {
                ajax_return([
                    'success' => false,
                    'message' => 'Authentication required. Please log in.',
                    'redirect' => site_url('login')
                ]);
                exit();
            } else {
                // For regular requests
                redirect('login');
            }
        }
    }
    
    public function files() {
        // Authentication already checked in constructor
        
        $files = $this->get_files();

        $this->load->view('cms/browser/view_files', array(
            'files' => $files
        ));
    }

    public function upload() {
        // Authentication already checked in constructor
        
        // ============ ADDED CSRF PROTECTION ============
        // Check if this is a POST request (form submission)
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                show_error('Invalid CSRF token', 400);
                return;
            }
            
            // Process form submission here if needed
            // (Currently empty, but protected if added later)
        }
        // ============ END CSRF PROTECTION ============
        
        // Load view for GET requests (display form)
        $this->load->view('cms/browser/view_upload', array(
            'heading' => ''
        ));
    }

    public function ajax_upload_file() {
    // Authentication already checked in constructor
    
    // ============ ADDED CSRF PROTECTION ============
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        ajax_return([
            'success' => false,
            'message' => 'Invalid CSRF token',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    // ============ END CSRF PROTECTION ============
    
    // ============ UPLOAD DIRECTORY SECURITY ============
    $uploadDir = 'resources/ckuploads';
    
    // Ensure directory exists with proper permissions
    if (!is_dir(FCPATH . $uploadDir)) {
        mkdir(FCPATH . $uploadDir, 0755, true);
    }
    
    // Add .htaccess to prevent PHP execution in upload directory
    $htaccessContent = <<<HTACCESS
# Prevent PHP execution in upload directory
<FilesMatch "\.(php|php5|php7|phtml|phps)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes
HTACCESS;
    
    $htaccessPath = FCPATH . $uploadDir . '/.htaccess';
    if (!file_exists($htaccessPath)) {
        file_put_contents($htaccessPath, $htaccessContent);
    }
    
    $options['uploadDir'] = FCPATH . $uploadDir . '/';
    $options['title'] = 'auto';
    $options['extensions'] = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $options['maxSize'] = 10 * 1024 * 1024; // 10MB limit
    
    // Optional: Rename files to prevent overwriting
    $options['replace'] = false;

    $this->load->library('file_uploader', array(
        'name' => 'ckfiles',
        'options' => $options
    ));

    // Call to upload the files
    $data = $this->file_uploader->upload();
    
    // Optional: Log successful uploads
    if (isset($data['files'][0]['name'])) {
        $userData = $this->session->userdata('login');
        log_message('info', 'File uploaded: ' . $data['files'][0]['name'] . 
                   ' by user ' . ($userData['id'] ?? 'unknown'));
    }

    ajax_return($data);
}

  public function ajax_remove_file() {
    // Authentication already checked in constructor
    
    // ============ ADDED CSRF PROTECTION ============
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        ajax_return([
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page.',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    // ============ END CSRF PROTECTION ============
    
    if ($this->input->post('file')) {
        $filename = $this->input->post('file');
        
        // ============ PATH TRAVERSAL PROTECTION ============
        // 1. Validate filename format
        if (empty($filename) || !is_string($filename)) {
            ajax_return([
                'success' => false,
                'message' => 'Invalid filename provided.'
            ]);
            return;
        }
        
        // 2. Remove any null bytes (security precaution)
        $filename = str_replace(chr(0), '', $filename);
        
        // 3. Prevent directory traversal attacks
        if (strpos($filename, '..') !== false || 
            strpos($filename, '/') !== false || 
            strpos($filename, '\\') !== false ||
            preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename) !== 1) {
            log_message('error', 'Path traversal attempt detected: ' . $filename);
            ajax_return([
                'success' => false,
                'message' => 'Invalid filename format. Security violation detected.'
            ]);
            return;
        }
        
        // 4. Define base directory and full path
        $baseDir = 'resources/ckuploads/';
        $filePath = $baseDir . $filename;
        
        // 5. Ensure the file exists within the intended directory
        $realBasePath = realpath(FCPATH . $baseDir);
        $realFilePath = realpath(FCPATH . $filePath);
        
        if ($realFilePath === false) {
            // File doesn't exist
            ajax_return([
                'success' => false,
                'message' => 'File not found.'
            ]);
            return;
        }
        
        // 6. Check if file is within the allowed directory
        if (strpos($realFilePath, $realBasePath) !== 0) {
            log_message('error', 'Path traversal attempt: ' . $realFilePath . ' not in ' . $realBasePath);
            ajax_return([
                'success' => false,
                'message' => 'Security violation: File path outside allowed directory.'
            ]);
            return;
        }
        
        // 7. Additional safety check - file must be a regular file
        if (!is_file($realFilePath)) {
            ajax_return([
                'success' => false,
                'message' => 'Not a valid file.'
            ]);
            return;
        }
        
        // ============ PERMISSION VALIDATION (OPTIONAL) ============
        // If you want to check who uploaded the file:
        // $this->check_file_ownership($filename, $this->session->userdata('login'));
        
        // ============ LOGGING FOR AUDIT TRAIL ============
        $userData = $this->session->userdata('login');
        $userId = isset($userData['id']) ? $userData['id'] : 'unknown';
        $userType = isset($userData['type']) ? $userData['type'] : 'unknown';
        
        log_message('info', 'File deletion: ' . $filename . ' by user ' . $userId . ' (' . $userType . ')');
        
        // ============ FILE DELETION ============
        try {
            // Remove physical file
            if (delete_file($realFilePath)) {
                // Optional: Clean up any thumbnails or related files
                $this->cleanup_related_files($filename);
                
                ajax_return([
                    'success' => true,
                    'message' => 'File deleted successfully.'
                ]);
            } else {
                ajax_return([
                    'success' => false,
                    'message' => 'Failed to delete file. Please check file permissions.'
                ]);
            }
        } catch (Exception $e) {
            log_message('error', 'File deletion error: ' . $e->getMessage());
            ajax_return([
                'success' => false,
                'message' => 'An error occurred while deleting the file.'
            ]);
        }
    } else {
        ajax_return([
            'success' => false,
            'message' => 'No file specified for deletion.'
        ]);
    }
}

// ============ HELPER METHOD FOR RELATED FILES ============
private function cleanup_related_files($filename) {
    // If you have thumbnail versions or cached versions, clean them up
    $baseDir = 'resources/ckuploads/';
    
    // Example: Remove thumbnail if it exists
    $thumbPath = $baseDir . 'thumbs/' . $filename;
    if (file_exists(FCPATH . $thumbPath)) {
        delete_file(FCPATH . $thumbPath);
    }
    
    // Example: Remove any cached/resized versions
    $cachePattern = $baseDir . 'cache/*_' . $filename;
    $cacheFiles = glob(FCPATH . $cachePattern);
    foreach ($cacheFiles as $cacheFile) {
        if (is_file($cacheFile)) {
            delete_file($cacheFile);
        }
    }
}

// ============ OPTIONAL: FILE OWNERSHIP CHECK ============
private function check_file_ownership($filename, $userData) {
    // If you store file metadata in database, check ownership here
    // This prevents users from deleting files they don't own
    
    $this->load->database();
    
    $query = $this->db->where('filename', $filename)
                     ->where('user_id', $userData['id'])
                     ->where('user_type', $userData['type'])
                     ->get('file_uploads');
    
    if ($query->num_rows() === 0) {
        // User doesn't own this file
        ajax_return([
            'success' => false,
            'message' => 'You do not have permission to delete this file.'
        ]);
        return false;
    }
    
    return true;
}

    private function get_files() {
    dir_create('resources/ckuploads');
    $path = abs_path() . 'resources/ckuploads';
    $fileData = array();

    if ($handle = opendir($path)) {
        while (FALSE !== ($file = readdir($handle))) {
            if ($file === '.' || $file === 'next' || $file === '..') continue;
            
            $filePath = $path . '/' . $file;
            
            // Skip files over 50MB
            if (filesize($filePath) > 50 * 1024 * 1024) {
                continue;
            }
            
            // Get safe MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            
            // Only allow safe file types
            $safeTypes = [
                'image/jpeg', 'image/png', 'image/gif', 
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            
            if (!in_array($mimeType, $safeTypes)) {
                continue;
            }
            
            $fileData[] = array(
                'name' => $file,
                'type' => $mimeType,
                'size' => filesize($filePath),
                'file' => site_url() . 'resources/ckuploads/' . $file,
                'data' => array(
                    'url' => site_url() . 'resources/ckuploads/' . $file,
                    'filename' => $file
                )
            );
        }
        closedir($handle);
    }

    return $fileData;
}
}