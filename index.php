<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\Project;
use App\Publication;
use App\Parser\BibTeXParser;
use App\Parser\RISParser;
use App\Exporter\ExcelExporter;

session_start();

// Initialize database
Database::connect();

// Get URL path
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$segments = $url ? explode('/', $url) : [];

// Router
$action = $segments[0] ?? 'home';

// Helper function to redirect
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Helper function to render view
function view(string $name, array $data = []): void
{
    extract($data);
    require __DIR__ . '/views/' . $name . '.php';
}

// Helper function for JSON response
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Routes
switch ($action) {
    case '':
    case 'home':
        // List all projects
        $projects = Project::all();
        foreach ($projects as &$project) {
            $project['publication_count'] = Project::getPublicationCount($project['id']);
            $project['selected_count'] = Project::getSelectedCount($project['id']);
        }
        view('projects/index', ['projects' => $projects]);
        break;

    case 'project':
        $subAction = $segments[1] ?? 'list';

        if ($subAction === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Create new project
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $keywords = $_POST['keywords'] ?? '';

            if (empty($name)) {
                $_SESSION['error'] = 'Project name is required';
                redirect('/');
            }

            $projectId = Project::create($name, $description, $keywords);
            $_SESSION['success'] = 'Project created successfully';
            redirect('/project/view/' . $projectId);
        } elseif ($subAction === 'view' && isset($segments[2])) {
            // View project publications
            $projectId = (int) $segments[2];
            $project = Project::find($projectId);

            if (!$project) {
                $_SESSION['error'] = 'Project not found';
                redirect('/');
            }

            $publications = Publication::findByProject($projectId);
            view('projects/view', [
                'project' => $project,
                'publications' => $publications,
            ]);
        } elseif ($subAction === 'edit' && isset($segments[2])) {
            // Edit project
            $projectId = (int) $segments[2];
            $project = Project::find($projectId);

            if (!$project) {
                $_SESSION['error'] = 'Project not found';
                redirect('/');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $keywords = $_POST['keywords'] ?? '';

                if (empty($name)) {
                    $_SESSION['error'] = 'Project name is required';
                } else {
                    Project::update($projectId, $name, $description, $keywords);
                    $_SESSION['success'] = 'Project updated successfully';
                    redirect('/project/view/' . $projectId);
                }
            }

            view('projects/edit', ['project' => $project]);
        } elseif ($subAction === 'delete' && isset($segments[2])) {
            // Delete project
            $projectId = (int) $segments[2];
            Project::delete($projectId);
            $_SESSION['success'] = 'Project deleted successfully';
            redirect('/');
        } elseif ($subAction === 'upload' && isset($segments[2]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Upload files to project
            $projectId = (int) $segments[2];
            $project = Project::find($projectId);

            if (!$project) {
                jsonResponse(['error' => 'Project not found'], 404);
            }

            if (!isset($_FILES['files'])) {
                jsonResponse(['error' => 'No files uploaded'], 400);
            }

            $files = $_FILES['files'];
            $totalImported = 0;

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $filename = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $content = file_get_contents($tmpName);

                // Determine file type and parse
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $publications = [];

                try {
                    if ($ext === 'bib') {
                        $publications = BibTeXParser::parse($content);
                    } elseif ($ext === 'ris') {
                        $publications = RISParser::parse($content);
                    } else {
                        continue;
                    }

                    // Import publications
                    foreach ($publications as $pub) {
                        $pub['source_file'] = $filename;
                        Publication::create($projectId, $pub);
                        $totalImported++;
                    }
                } catch (\Exception $e) {
                    // Skip problematic files
                    continue;
                }
            }

            Project::touch($projectId);
            jsonResponse(['success' => true, 'imported' => $totalImported]);
        }
        break;

    case 'publication':
        $subAction = $segments[1] ?? '';

        if ($subAction === 'toggle' && isset($segments[2])) {
            // Toggle publication selection
            $publicationId = (int) $segments[2];
            Publication::toggleSelected($publicationId);
            jsonResponse(['success' => true]);
        }
        break;

    case 'export':
        // Export selected publications
        $projectId = (int) ($segments[1] ?? 0);
        $project = Project::find($projectId);

        if (!$project) {
            $_SESSION['error'] = 'Project not found';
            redirect('/');
        }

        $publications = Publication::getSelected($projectId);

        if (empty($publications)) {
            $_SESSION['error'] = 'No publications selected';
            redirect('/project/view/' . $projectId);
        }

        $filename = ExcelExporter::export($publications, $project['name']);

        // Download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        unlink($filename); // Delete after download
        exit;

    default:
        // 404
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        break;
}
