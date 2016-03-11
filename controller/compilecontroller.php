<?php
namespace OCA\Files_Latexeditor\Controller;
use OCP\Files;
use OC\HintException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
class CompileController extends Controller
{
/** @var IL10N */
    private $l;
    /** @var View */
    private $view;
    /** @var ILogger */
    private $logger;
    public $request;
    /**

    * @NoAdminRequired

    *
    * @param string $AppName
    * @param IRequest $request
    * @param IL10N $l10n
    * @param View $view
    * @param ILogger $logger

    */
    public function __construct($AppName, IRequest $request, IL10N $l10n, ILogger $logger)
    {
        parent::__construct($AppName, $request);
        $this->l = $l10n;
        $this->logger = $logger;
        $this->request = $request;
    }
    
    /**
    * Compile Latex File
    *
    * @NoAdminRequired
    *
    * @param string $path
    * @param string $filename
    * @param string $compiler
    * @return DataResponse
    */
    public function doCompile($path, $filename, $compiler)
    {
        $success = 'success';
        $error = 'error';
        try
        {
            set_time_limit(0); //scanning can take ages
            // If they've set the compiler to something other than an allowable option....
            if (!($compiler === 'xelatex' || $compiler === 'pdflatex' || $compiler === 'latex'))
            {
                $compiler = 'latex';
            }
            // The real directory file
            $workdir = dirname(\OC\Files\Filesystem::getLocalFile(stripslashes($path) . $filename));
            $info = pathinfo($filename);
            $fileext = '.' . $info['extension'];
            $projectname = trim(basename($filename, $fileext));
            $pdffile = $projectname . '.pdf';
            $dvifile = $projectname . '.dvi';
            $psfile  = $projectname . '.ps';
            $tocfile = $projectname . '.toc';
            $logfile = $projectname . '.log';
            $bibfile = $projectname; // Bibtex File is without extension
            // As we will write pdf/ps file(s) in the $path, we need to known if it's writable
            if (!\OC\Files\Filesystem::isCreatable(stripslashes($path)))
            {
                return new JSONResponse(array('data' => array('message' => 'As you don\'t have write permission in the owner directory, it\'s not possible to create output latex files.', 'output' => '')), Http::STATUS_BAD_REQUEST);
            }
            // Command to jump into directory
            $cd_command = "cd " . str_replace(' ', '\ ', trim($workdir));
            // PDFLatex command preparation
            if ($compiler == 'xelatex' || $compiler == 'pdflatex')
            {
                $latex_command = $compiler . ' ' . $filename;
                $bibtex_command = 'bibtex ' . $bibfile;
            }
            // LaTeX command preparation
            else
            {
                $latex_command = "latex -output-directory=$outpath  $filename ; cd $outpath; dvips  $dvifile ; ps2pdf $psfile";
            }
            $output = "<b>========BEGIN COMPILE========</b>\n$psfile \n";
            $output .= $cd_command . "\n";
            $output .= getcwd() . "\n";
            // First Compile
            $output .= shell_exec($cd_command . " && pwd");
            $return = shell_exec($cd_command . " && " . $latex_command);
            $output .= getcwd() . "\n";
            // For BibTeX
            if ($compiler == 'pdflatex')
            {
            // Second compile step with bibtex
                $return .= shell_exec($cd_command . " && " . $bibtex_command);
                // compile again after bibtex
                $return .= shell_exec($cd_command . " && " . $latex_command);
            }
            $logfile = $workdir.'/'.$logfile; 
            $log = file_get_contents($logfile);
            while (preg_match('/Return to get cross-references right/', $log) || preg_match('/No file ' . $tocfile . '/', $log))
            {
                $return .= shell_exec($cd_command . " && " . $this->latex_command);
                $log = file_get_contents($logfile);
            }
            // ! at begining of a line indicate an error!
            $errors = preg_grep("/^!/", explode("\n", $log));
            if(empty($errors) === false)
            {
                $log_array = explode("\n", $log);
                $error = "\n";
                foreach ($errors as $line => $msg)
                {
                    for ($i = $line; $i <= $line + 5; $i++)
                    {
                        $error .= $log_array[$i] . "\n";
                    }
                }
                return new JSONResponse(array('data' => array('message' => $this->l->t('Compile failed with errors') . ' - <br/>', 'output' => nl2br($output . " % " . $latex_command . "\n" . $error)), 'status'=>$error), Http::STATUS_OK);
            }
            // No PDF File !?
            if (!file_exists($workdir . '/' . $pdffile))
            {
                return new JSONResponse(array('data' => array('message' => $this->l->t('Compile failed with errors') . ':<br/>', 'output' => nl2br($output . " % " . $latex_command . "\n" . file_get_contents($outpath . '/' . $logfile))), 'status'=>$error), Http::STATUS_OK);
            }
            ;
            $output .= $return;
            $output .= "\n========END COMPILE==========\n";
            $oc_workdir = stripslashes(\OC\Files\Filesystem::getLocalPath($workdir));
            $target = \OCP\Files::buildNotExistingFileName($oc_workdir, $pdffile);
            $target = \OC\Files\Filesystem::normalizePath($target);
            $meta = \OC\Files\Filesystem::getFileInfo($target);
            if ($compiler === 'latex')
            {
                $target = \OCP\Files::buildNotExistingFileName($oc_workdir, $psfile);
                $target = \OC\Files\Filesystem::normalizePath($target);
                $meta = \OC\Files\Filesystem::getFileInfo($target);
            }
            return new JSONResponse(array('data' => array('output' => nl2br($output), 'path' => $path, 'pdffile' => $pdffile, 'psfile' => $psfile, 'logfile' => $logfile), 'status'=> $success), Http::STATUS_OK);
        }
        catch (\Exception $e)
        {
            return new DataResponse(['message' => $e], Http::STATUS_BAD_REQUEST);
        }
    }
}