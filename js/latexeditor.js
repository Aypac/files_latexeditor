function onEditorTrigger(filename, context)
{
    // ActionHandler to open .tex file in Files_Texteditor
    OCA.Files_Texteditor.currentContext = context;
    OCA.Files_Texteditor.file.name = filename;
    OCA.Files_Texteditor.file.dir = context.dir;
    OCA.Files_Texteditor.fileInfoModel = context.fileList.getModelForFile(filename);
    OCA.Files_Texteditor.loadEditor(
        OCA.Files_Texteditor.$container,
        OCA.Files_Texteditor.file
    );
    if(!$('html').hasClass('ie8'))
    {
        history.pushState(
            {
            file: filename, dir: context.dir
            }
            , 'Editor', '#editor');
    }
}


function registerLatexFileType()
{
    var mimes = Array('application/x-tex');
    $.each(mimes, function(key, value)
        {
            OCA.Files.fileActions.registerAction(
                {
                name: 'Edit',
                mime: value,
                actionHandler: onEditorTrigger,
                permissions: OC.PERMISSION_READ,
                icon: function ()
                    {
                        return OC.imagePath('core', 'actions/edit');
                    }
                }
            );
            OCA.Files.fileActions.setDefault(value, 'Edit');
        }
    );
}


function addLatexButton()
{
    if(isLatex(OCA.Files_Texteditor.file.name))
    {
        var latexbutton = '<button id="editor_compile">'+t('files_latexeditor', 'LaTeX')+'</button>';
        $('#editor_close').after(latexbutton);
        $('#editor_compile').click(doCompile);
        console.log("Added LateX button to editor");
    }
}

$(document).ready(function ()
    {
        // enable console
        delete console.log;
        // Register Latex
        registerLatexFileType();
        // Add Latex Button
        $('#editor_close').livequery(function() {
            addLatexButton();
        });
        // Set Request Token for each AJAX reuqest
        $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
          jqXHR.setRequestHeader('requesttoken', oc_requesttoken);
        });
    }
);
function getFileExtension(file)
{
    try{
        var parts = file.split('.');
        return parts[parts.length-1];
    }
    catch(err)
    {
        return '';
    }
}
function isLatex(filename)
{
    return getFileExtension(filename)=='tex' || getFileExtension(filename)=='latex';
}
function AjaxCompile(ajaxpath, path, filename, pdflatex)
{
    console.log('Begin LaTeX compiling process...');
    console.log('Path: '+path+' Filename: '+filename+' Compiler: '+pdflatex);
    var jqxhr = $.ajax(
        {
        type: 'POST',
        url: ajaxpath, // '/apps/files_texteditor/ajax/savefile'
        data:
            {
            path: path,
            filename: filename,
            compiler: pdflatex
            }
            ,
        dataType: 'json',
        global: false,
        async: false,
        beforeSend: function()
            {
                // Save file again
                OCA.Files_Texteditor._onSaveTrigger();
            }
            ,
        success: function(json)
            {
                return json;
            }
        }
    ).responseText;
    return jQuery.parseJSON(jqxhr);
}

function compileFile(filename, path)
{
    console.log("Dialog visible");
    var is_compiled = false;
    var ajaxpath = OC.generateUrl('/apps/files_latexeditor/ajax/compile');
    var pdffile = "";
    var data = "";
    DestroIfExist("dialogcompile");
    var compileDlg = $('<div id="dialogcompile"  title="'+'Compiling:'+ path+filename +'"><div id="latexresult" class="" style="width:98%;height:98%;">'+t('files_latexeditor', "Choose the compiler and click on 'Compile'...")+'</div></div>').dialog(
        {
        modal: false,
        open: function(e, ui)
            {
                // Make Dialog Visible
                $('.ui-dialog').css('z-index',10000);
                // Find dummy entry and replace it with Latex compiler chooser
                $(e.target).parent().find('span').filter(function(){return $(this).text() === 'dummy';}
                ).parent().replaceWith('<select id="compiler" name ="compiler"><option value="pdflatex" selected>PDFLaTeX</option><option value="latex">LaTeX</option><option value="bibtex">BibTex</option></select>');
            }
            ,
        buttons:
            {
            'dummy': function(e){},
            Compile: function()
                {
                    is_compiled = true;
                    $('#latexresult').html("Compiling...");
                    json = AjaxCompile(ajaxpath, path, filename, $('#compiler').val());
                    if(json)
                    {
                        $('#latexresult').html("");
                        if(json.status=='success')
                        {
                            $('#latexresult').removeClass('ui-state-error');
                            $('#latexresult').css({color: "darkblue"});
                            $(":button:contains('ViewPdf')").button('enable');
                        }
                        else
                        {
                            $('#latexresult').html(json.data.message);
                            $('#latexresult').css({color: "red"});
                            $(":button:contains('ViewPdf')").button('disable');
                        }
                        $('#latexresult').append(json.data.output);
                        // Auto Scroll Down
                        $('#dialogcompile').animate({scrollTop:$('#latexresult')[0].scrollHeight}, 'slow');
                        // Update OwnCloud File Chache via files API
                        console.log('Scanning files in '+path);
                        scanFiles(true, path, ''); 
                    }
                }
                ,
            ViewPdf: function()
                {
                    // Make PDF Viewer Path
                    var pdfviewerpath = '/owncloud/index.php/apps/files_pdfviewer/?file=';
                    // Make PDF Viewer Parameters
                    var pdfparam = OC.linkTo('files', 'ajax/download.php')
                    +'?dir='
                    + encodeURIComponent(json.data.path)
                    +'&files='+encodeURIComponent(json.data.pdffile);
                    // Combine Path & Params (need double encoding!!!)
                    pdfviewerpath = pdfviewerpath + encodeURIComponent(pdfparam);
                    frame = '<iframe id="latexresultpdf" style="width:100%;height:100%;display:block;"></iframe>';
                    $('#latexresult').html(frame).promise().done(function()
                        {
                            $('#latexresultpdf').attr('src', pdfviewerpath);
                        }
                    );
                }
                ,
            Close: function()
                {
                    $(this).dialog("close");
                }
            }
        }
    )

    x = $('#editor').position().left+$('#editor').width()*0.45;
    y = $('#editor').position().top+10;
    compileDlg.dialog(
        {
        width: $('#editor').width()*0.8,
        height: $('#editor').height()*0.85,
        position:[x, y]
        }
    );
    $(":button:contains('ViewPdf')").button('disable');

}
// Try to compile
function doCompile()
{
    if($('#editor_container').is(":visible"))
    {
        var filename = OCA.Files_Texteditor.file.name;
        var dir = OCA.Files_Texteditor.file.dir + '/';
        compileFile(filename, dir);
    }
}
function DestroIfExist(idname)
{
    if(document.getElementById(idname))
    {
        $("#"+idname).remove();
    }
}