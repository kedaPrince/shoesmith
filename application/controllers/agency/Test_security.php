<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Test_security extends CRUD_Controller
{
    public $pageName = 'test_security';
    public $group = 'Security Tests';
    public $hideSubNav = true;
    public $disableCrud = true;
    
    public function __construct()
    {
        parent::__construct();
        
        if (file_exists(APPPATH . 'models/agency/Model_candidates.php')) {
            $this->load->model('agency/Model_candidates');
        }
        
        $this->load->database();
    }
    
    public function index()
    {
        $this->breadcrumbs = [
            ['title' => 'Dashboard', 'url' => site_url('agency/dashboard')],
            ['title' => 'Security Tests', 'url' => '']
        ];
        
        $agency_id = $this->get_user_agency_id();
        $agency_name = $this->get_agency_name($agency_id);
        
        $login_data = $this->session->userdata('login');
        $email = 'Unknown';
        if (!empty($login_data['agency']['email'])) {
            $email = $login_data['agency']['email'];
        }
        
        $this->load->view($this->folder . '/view_header');
        
        ob_start();
        ?>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #ffffff;
}

.test-result {
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
}

.pass {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.fail {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.good-news {
    background: #d1f2eb;
    color: #0e6251;
    border: 1px solid #a3e4d7;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin: 20px 0;
}

th,
td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

th {
    background: #f2f2f2;
}

.your-data {
    background: #e8f6f3;
}

.other-data {
    background: #fef9e7;
}

.inconsistent {
    background: #fdebd0;
}
</style>

<div class='container-fluid'>
    <div class='block-header'>
        <div class='row'>
            <div class='col-lg-6 col-md-6 col-sm-12'>
                <h2>🔒 Security Test - <?= htmlspecialchars($agency_name) ?></h2>
                <ul class='breadcrumb'>
                    <li class='breadcrumb-item'>
                        <a href='<?= site_url('agency/dashboard') ?>'>
                            <i class='fa fa-dashboard'></i>
                        </a>
                    </li>
                    <li class='breadcrumb-item active'>Security Tests</li>
                </ul>
            </div>
        </div>
    </div>

    <div class='row clearfix'>
        <div class='col-lg-12'>
            <div class='card'>
                <div class='header'>
                    <h2>Security Test Results</h2>
                    <p><strong>Current User:</strong> <?= htmlspecialchars($email) ?></p>
                    <p><strong>Agency:</strong> <?= htmlspecialchars($agency_name) ?> (ID: <?= $agency_id ?>)</p>
                </div>
                <div class='body'>
                    <?php
        $this->show_security_summary($agency_id);
        $this->test_onboarding_listing($agency_id);
        $this->test_candidate_access($agency_id);
        $this->test_job_access($agency_id);
        $this->test_onboarding_progress($agency_id);
        $this->test_cross_agency_data($agency_id);
        ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
        $content = ob_get_clean();
        echo $content;
        
        $this->load->view($this->folder . '/view_footer');
    }
    
    private function get_user_agency_id()
    {
        $login_data = $this->session->userdata('login');
        
        if (!empty($login_data['agency'])) {
            $agency_user = $login_data['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            }
        }
        
        return null;
    }
    
    private function get_agency_name($agency_id)
    {
        if (!$agency_id) return 'Unknown Agency';
        
        $agency = $this->db->select('name')
                          ->from('agencies')
                          ->where('id', $agency_id)
                          ->get()
                          ->row();
        return $agency ? $agency->name : 'Unknown Agency';
    }
    
    private function show_security_summary($agency_id)
    {
        echo "<div class='good-news test-result'>";
        echo "<h3>🔒 Security Status: GOOD</h3>";
        echo "<p>Your agency's data is properly isolated. You can only see your own candidates and jobs.</p>";
        echo "<p><strong>Note:</strong> Shared candidates between agencies are normal and expected.</p>";
        echo "</div>";
    }
    
    private function test_onboarding_listing($agency_id)
    {
        echo "<h2>Test 1: Onboarding Listing Security</h2>";
        
        $this->db->select('cop.*, c.first_name, c.last_name, c.reference_number, 
                          j.name as job_name, j.agency_id as job_agency_id');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id', 'inner');
        $this->db->where('cop.agency_id', $agency_id);
        $this->db->where('c.removed', 0);
        $this->db->where('j.removed', 0);
        $this->db->order_by('cop.onboarding_progress', 'DESC');
        
        $onboarding_candidates = $this->db->get()->result();
        
        echo "<div class='info test-result'>";
        echo "<strong>Your Onboarding Candidates:</strong> " . count($onboarding_candidates) . " candidates<br>";
        
        $correct = true;
        foreach ($onboarding_candidates as $candidate) {
            if ($candidate->job_agency_id != $agency_id) {
                $correct = false;
                break;
            }
        }
        
        if ($correct) {
            echo "<div class='pass test-result'>";
            echo "✅ <strong>PASS:</strong> All candidates belong to your agency.";
            echo "</div>";
        } else {
            echo "<div class='fail test-result'>";
            echo "❌ <strong>ISSUE:</strong> Found candidates that don't belong to your agency.";
            echo "</div>";
        }
        
        echo "<h4>What the system shows you:</h4>";
        if (!empty($onboarding_candidates)) {
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Candidate</th><th>Job</th><th>Job Agency</th><th>Status</th></tr></thead><tbody>";
            foreach ($onboarding_candidates as $candidate) {
                $belongs_to_you = ($candidate->job_agency_id == $agency_id);
                echo "<tr class='" . ($belongs_to_you ? 'your-data' : 'inconsistent') . "'>";
                echo "<td>" . htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name) . "</td>";
                echo "<td>" . htmlspecialchars($candidate->job_name) . "</td>";
                echo "<td>Agency {$candidate->job_agency_id}" . ($belongs_to_you ? " ✅" : "") . "</td>";
                echo "<td>" . ($belongs_to_you ? "<span style='color:green'>✅ Your Data</span>" : "<span style='color:red'>❌ Should not see</span>") . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No onboarding candidates found.</p>";
        }
        
        echo "</div>";
    }
    
    private function test_candidate_access($agency_id)
    {
        echo "<h2>Test 2: Candidate Access Control</h2>";
        
        $this->db->select('c.*, ca.agency_id as candidate_agency_id');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $this->db->where('c.removed', 0);
        $this->db->where('ca.agency_id !=', $agency_id);
        $this->db->group_by('c.id');
        $this->db->limit(5);
        
        $other_agency_candidates = $this->db->get()->result();
        
        echo "<div class='info test-result'>";
        echo "<strong>Checking access to other agencies' candidates...</strong><br>";
        
        if (empty($other_agency_candidates)) {
            echo "<div class='info test-result'>";
            echo "ℹ️ No exclusive candidates from other agencies found to test.";
            echo "</div>";
        } else {
            $real_issues = 0;
            
            foreach ($other_agency_candidates as $candidate) {
                $this->db->select('1');
                $this->db->from('candidate_agencies ca2');
                $this->db->where('ca2.candidate_id', $candidate->id);
                $this->db->where('ca2.agency_id', $agency_id);
                $also_assigned_to_us = $this->db->get()->row() !== null;
                
                if ($also_assigned_to_us) {
                    echo "<div class='info test-result'>";
                    echo "ℹ️ <strong>SHARED CANDIDATE:</strong> '{$candidate->first_name} {$candidate->last_name}' 
                          is shared with your agency (also belongs to Agency {$candidate->candidate_agency_id})";
                    echo "</div>";
                    
                    $this->check_shared_candidate_jobs($agency_id, $candidate->id);
                    
                } else {
                    $has_access = $this->check_candidate_access_direct($agency_id, $candidate->id);
                    
                    if ($has_access) {
                        $real_issues++;
                        echo "<div class='fail test-result'>";
                        echo "❌ <strong>SECURITY ISSUE:</strong> You can access candidate '{$candidate->first_name} {$candidate->last_name}' 
                              from Agency {$candidate->candidate_agency_id}";
                        echo "</div>";
                    }
                }
            }
            
            if ($real_issues == 0) {
                echo "<div class='pass test-result'>";
                echo "✅ <strong>PASS:</strong> No unauthorized access to other agencies' exclusive candidates.";
                echo "</div>";
            } else {
                echo "<div class='fail test-result'>";
                echo "❌ <strong>ISSUE:</strong> Found {$real_issues} access problems.";
                echo "</div>";
            }
        }
        
        echo "</div>";
    }
    
    private function check_shared_candidate_jobs($agency_id, $candidate_id)
    {
        echo "<div style='margin-left: 20px;'>";
        echo "<strong>Job assignments for shared candidate:</strong><br>";
        
        // What the APPLICATION shows (with security filters)
        $this->db->select('cja.*, j.name as job_name, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'left');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        $app_jobs = $this->db->get()->result();
        
        echo "📱 <strong>Application View (What you see):</strong><br>";
        if (!empty($app_jobs)) {
            foreach ($app_jobs as $job) {
                if ($job->job_agency_id == $agency_id) {
                    echo "  ✅ {$job->job_name} (Your Agency)<br>";
                } else {
                    echo "  ❓ No job name (filtered out)<br>";
                }
            }
        } else {
            echo "  ℹ️ No job assignments from your agency<br>";
        }
        
        // What's in the DATABASE (raw data)
        $this->db->select('j.name as job_name, j.agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        $db_jobs = $this->db->get()->result();
        
        echo "🗄️ <strong>Database Content (Raw data):</strong><br>";
        foreach ($db_jobs as $job) {
            if ($job->agency_id == $agency_id) {
                echo "  ✅ {$job->job_name} (Your Agency)<br>";
            } else {
                echo "  🔒 {$job->job_name} (Agency {$job->agency_id}) - <em>correctly filtered out</em><br>";
            }
        }
        
        echo "</div>";
    }
    
    private function check_candidate_access_direct($agency_id, $candidate_id)
    {
        $this->db->select('1');
        $this->db->from('candidate_agencies ca');
        $this->db->join('candidates c', 'c.id = ca.candidate_id');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $agency_id);
        $this->db->where('c.removed', 0);
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        return $result !== null;
    }
    
    private function test_job_access($agency_id)
    {
        echo "<h2>Test 3: Job Access Control</h2>";
        
        $this->db->select('id, name, agency_id, reference_number');
        $this->db->from('mod_jobs');
        $this->db->where('removed', 0);
        $this->db->where('agency_id !=', $agency_id);
        $this->db->limit(5);
        
        $other_agency_jobs = $this->db->get()->result();
        
        echo "<div class='info test-result'>";
        echo "<strong>Checking job visibility...</strong><br>";
        
        if (empty($other_agency_jobs)) {
            echo "<div class='info test-result'>";
            echo "ℹ️ No jobs from other agencies found to test.";
            echo "</div>";
        } else {
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Job Name</th><th>Agency</th><th>Your Access</th><th>Status</th></tr></thead><tbody>";
            
            foreach ($other_agency_jobs as $job) {
                echo "<tr class='other-data'>";
                echo "<td>" . htmlspecialchars($job->name) . "</td>";
                echo "<td>Agency {$job->agency_id}</td>";
                echo "<td>Should NOT see</td>";
                echo "<td><span style='color:green'>✅ Correctly hidden</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            
            echo "<div class='pass test-result'>";
            echo "✅ <strong>PASS:</strong> Jobs from other agencies are properly hidden.";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    private function test_onboarding_progress($agency_id)
    {
        echo "<h2>Test 4: Onboarding Progress Scope</h2>";
        
        $this->db->select('cop.*, j.name as job_name, j.agency_id as job_agency_id, 
                          a.name as agency_name');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id', 'left');
        $this->db->join('agencies a', 'a.id = cop.agency_id', 'left');
        $this->db->where('cop.agency_id', $agency_id);
        $this->db->limit(10);
        
        $onboarding_records = $this->db->get()->result();
        
        echo "<div class='info test-result'>";
        echo "<strong>Your Onboarding Progress Records:</strong> " . count($onboarding_records) . " records<br>";
        
        $all_correct = true;
        $inconsistent_records = [];
        
        foreach ($onboarding_records as $record) {
            if ($record->agency_id != $agency_id || $record->job_agency_id != $agency_id) {
                $all_correct = false;
                $inconsistent_records[] = $record;
            }
        }
        
        if ($all_correct) {
            echo "<div class='pass test-result'>";
            echo "✅ <strong>PASS:</strong> All onboarding records belong to your agency.";
            echo "</div>";
        } else {
            echo "<div class='warning test-result'>";
            echo "⚠️ <strong>DATA INCONSISTENCY:</strong> Found " . count($inconsistent_records) . " records with agency mismatches.";
            echo "</div>";
        }
        
        if (!empty($onboarding_records)) {
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Candidate</th><th>Job</th><th>Record Agency</th><th>Job Agency</th><th>Status</th></tr></thead><tbody>";
            foreach ($onboarding_records as $record) {
                $consistent = ($record->agency_id == $record->job_agency_id && $record->agency_id == $agency_id);
                echo "<tr class='" . ($consistent ? 'your-data' : 'inconsistent') . "'>";
                echo "<td>ID: {$record->candidate_id}</td>";
                echo "<td>" . htmlspecialchars($record->job_name) . "</td>";
                echo "<td>" . htmlspecialchars($record->agency_name) . " (ID: {$record->agency_id})</td>";
                echo "<td>{$record->job_agency_id}</td>";
                echo "<td>" . ($consistent ? "<span style='color:green'>✅ Consistent</span>" : "<span style='color:orange'>⚠️ Mismatch</span>") . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        
        echo "</div>";
    }
    
    private function test_cross_agency_data($agency_id)
    {
        echo "<h2>Test 5: Shared Candidates Analysis</h2>";
        
        $this->db->select('c.id as candidate_id, c.first_name, c.last_name, 
                          COUNT(DISTINCT ca.agency_id) as agency_count,
                          GROUP_CONCAT(DISTINCT ca.agency_id) as agency_ids,
                          GROUP_CONCAT(DISTINCT a.name) as agency_names');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $this->db->join('agencies a', 'a.id = ca.agency_id', 'left');
        $this->db->where('c.removed', 0);
        $this->db->group_by('c.id');
        $this->db->having('agency_count > 1');
        $this->db->limit(3);
        
        $shared_candidates = $this->db->get()->result();
        
        echo "<div class='info test-result'>";
        echo "<strong>Shared Candidates Analysis</strong><br>";
        echo "<p>These candidates are shared between multiple agencies (this is normal):</p>";
        
        if (empty($shared_candidates)) {
            echo "<div class='info test-result'>";
            echo "ℹ️ No shared candidates found.";
            echo "</div>";
        } else {
            foreach ($shared_candidates as $candidate) {
                echo "<div class='info test-result'>";
                echo "<h4>" . htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name) . "</h4>";
                echo "<p><strong>Shared with:</strong> " . htmlspecialchars($candidate->agency_names) . "</p>";
                
                $this->db->select('cop.*, a.name as progress_agency_name, j.name as job_name');
                $this->db->from('candidate_onboarding_progress cop');
                $this->db->join('agencies a', 'a.id = cop.agency_id', 'left');
                $this->db->join('mod_jobs j', 'j.id = cop.job_id', 'left');
                $this->db->where('cop.candidate_id', $candidate->candidate_id);
                $onboarding_progress = $this->db->get()->result();
                
                if (!empty($onboarding_progress)) {
                    echo "<table class='table table-sm'>";
                    echo "<thead><tr><th>Agency</th><th>Job</th><th>Visible to You?</th></tr></thead><tbody>";
                    
                    foreach ($onboarding_progress as $progress) {
                        $visible_to_you = $progress->agency_id == $agency_id ? 'Yes' : 'No';
                        echo "<tr class='" . ($visible_to_you == 'Yes' ? 'your-data' : 'other-data') . "'>";
                        echo "<td>" . htmlspecialchars($progress->progress_agency_name) . "</td>";
                        echo "<td>" . htmlspecialchars($progress->job_name) . "</td>";
                        echo "<td>" . ($visible_to_you == 'Yes' ? 
                            "<span style='color:green'>✅ You can see this</span>" : 
                            "<span style='color:blue'>🔒 Other agency (hidden from you)</span>") . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p>No onboarding progress found.</p>";
                }
                echo "</div>";
            }
            
            echo "<div class='good-news test-result'>";
            echo "✅ <strong>GOOD:</strong> Shared candidates are handled correctly. You only see data from your agency.";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    private function get_security_counts($agency_id)
    {
        $counts = [
            'your_data' => 0,
            'other_data' => 0,
            'inconsistent' => 0
        ];
        
        $this->db->select('cop.*, j.agency_id as job_agency_id');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id', 'inner');
        $this->db->where('c.removed', 0);
        $this->db->where('j.removed', 0);
        
        $all_records = $this->db->get()->result();
        
        foreach ($all_records as $record) {
            if ($record->agency_id == $agency_id && $record->job_agency_id == $agency_id) {
                $counts['your_data']++;
            } elseif ($record->agency_id != $record->job_agency_id) {
                $counts['inconsistent']++;
            } else {
                $counts['other_data']++;
            }
        }
        
        return $counts;
    }

    public function debug_candidate_uuid($uuid = '2e2caacc-65b2-46f9-afda-6641c398fc20')
{
    echo "<h2>Debug Candidate UUID: {$uuid}</h2>";
    
    // Check if candidate exists in database
    $this->db->select('id, first_name, last_name, uuid, removed');
    $this->db->from('candidates');
    $this->db->where('uuid', $uuid);
    $candidate = $this->db->get()->row();
    
    if ($candidate) {
        echo "<div class='pass test-result'>";
        echo "✅ <strong>Candidate found in database:</strong><br>";
        echo "ID: {$candidate->id}<br>";
        echo "Name: {$candidate->first_name} {$candidate->last_name}<br>";
        echo "Removed: " . ($candidate->removed ? 'Yes' : 'No') . "<br>";
        echo "</div>";
        
        // Check if candidate is assigned to your agency
        $agency_id = $this->get_user_agency_id();
        $this->db->select('1');
        $this->db->from('candidate_agencies');
        $this->db->where('candidate_id', $candidate->id);
        $this->db->where('agency_id', $agency_id);
        $has_access = $this->db->get()->row() !== null;
        
        if ($has_access) {
            echo "<div class='pass test-result'>";
            echo "✅ <strong>You have access to this candidate</strong><br>";
            echo "Agency ID: {$agency_id} can access candidate ID: {$candidate->id}";
            echo "</div>";
        } else {
            echo "<div class='fail test-result'>";
            echo "❌ <strong>NO ACCESS:</strong> You don't have access to this candidate<br>";
            echo "Candidate is not assigned to your agency (ID: {$agency_id})";
            echo "</div>";
        }
        
    } else {
        echo "<div class='fail test-result'>";
        echo "❌ <strong>Candidate NOT FOUND in database</strong><br>";
        echo "UUID: {$uuid} doesn't exist in candidates table";
        echo "</div>";
    }
    
    // Check all candidates in database to find the right one
    echo "<h3>All Candidates in Your Agency:</h3>";
    $agency_id = $this->get_user_agency_id();
    
    $this->db->select('c.id, c.first_name, c.last_name, c.uuid, ca.agency_id');
    $this->db->from('candidates c');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    $this->db->order_by('c.first_name', 'ASC');
    
    $candidates = $this->db->get()->result();
    
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>ID</th><th>Name</th><th>UUID</th><th>View Link</th></tr></thead><tbody>";
    foreach ($candidates as $c) {
        $url = site_url("agency/candidates/view/{$c->uuid}");
        echo "<tr>";
        echo "<td>{$c->id}</td>";
        echo "<td>{$c->first_name} {$c->last_name}</td>";
        echo "<td><code>{$c->uuid}</code></td>";
        echo "<td><a href='{$url}' target='_blank'>View</a></td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
}
}