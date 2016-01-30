<?php
// Check LaTeX
$output=Array();                                                                                                                            
$return=1;                                                                                                                                  
exec('which latex',$output,$return);                                                                                                        
if (!$return && isset($output[0]))
{
        // only load latex editor if the user is logged in                                                                                  
        if (OCP\User::isLoggedIn() && OCP\App::isEnabled('files_texteditor'))
        {
                OCP\Util::addscript('files_latexeditor', 'latexeditor');                                                                    
        }                                                                                                                                   
}     
