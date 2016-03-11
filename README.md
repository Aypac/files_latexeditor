Fork of the <a href=""></a><a href="https://apps.owncloud.com/content/show.php/LatexTex+Editor+and+Compiler?content=151441">existing owncloud addon</a> all Credit to Khalatyan Arman (<a href="https://apps.owncloud.com/usermanager/search.php?username=arm2arm">arm2arm</a>) with updates from Alexander A.(<a href="https://apps.owncloud.com/usermanager/search.php?username=darksniper94">darksniper94</a>).

-<a href="https://github.com/arm2arm/files_latexeditor">Original Github-Repo</a>-

This repository is supposed to fix the bugs with OwnCloud 8.2.2+, since there has been no update since 2013 from Khalatyan Arman and none at all since 2015.

Hopefully one day we can even make it possible to get it running with encryption.

<b>If you want to work on this, PLEASE contact me via github or the owncloud-website. You can find instructions on how to write addons for OC <a href="https://doc.owncloud.org/server/8.2/developer_manual//app/">here</a>.</b>

How to install
=================
Remove the old owncloud/apps/files_latexeditor folder if existing.
Download and place the content of this repo into your owncloud/apps/ folder. The folder containing all the other folders and the README.md should be named files_latexeditor. Head over to you web-admin panel and get to the apps section. Press the small gear in the bottom left corner. Activate experimental apps. Go to "Not enabled" on the top left. Enable LaTeXEditor.
Make sure you have latex installed, otherwise this app will do nothing (you can check that with "which latex" which should return a path.


TODO
=================
<ul>
<li>Make it work with OwnCloud 8.2.2+</li>
<li>Create a new entry on https://apps.owncloud.com</li>
<li>Better Documentation</li>
<li>Fix the bugs with the encryption-addon enables</li>
</ul>

files_latexeditor
=================

File Latex Editor/compiler APP for OwnCloud 8.2.
You MUST enable the standard "Text Editor" to allow this one.


change log
=================
26.03.2015
OC8 compatibility.

17.09.2014
OC7 compatibility.

04.05.2014
Require Text Editor App 
Making latexeditor small as possible

01.12.2013
Remove all the files_texteditor code.
We detect now if the editor is open and then add (or not) the Compile button.

30.11.2013
compatibility with OC5
If directory contains a tex file friends can compile.
Double compilation for cross references.
Detect latex errors.
tested on OC5.0.13

10.01.2013 
code cleaninig
tested on OC4.5.5


