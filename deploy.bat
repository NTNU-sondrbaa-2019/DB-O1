@echo off
del /q c:\Extras\xampp\htdocs\IMT2571\Assignment1\model\*
del /q c:\Extras\xampp\htdocs\IMT2571\Assignment1\view\*
del /q c:\Extras\xampp\htdocs\IMT2571\Assignment1\controller\*
robocopy src c:\Extras\xampp\htdocs\IMT2571\Assignment1 /S /NJH
