@echo off
echo �Х��N������s���ɮש��d:\elearn.tmp �ؿ��U
echo �Y�|����m�A�Ы�Ctrl+C ���_�A�Y�w��m�h10����~��ާ@�C
echo ....
echo .......
choice /C YN /N /T 10 /D y > nul
echo �Y�N�� d:\elearn.tmp �ؿ��U���ɮ׽ƻs�� d:\elearn.git �ؿ��i���л\��s�C

xcopy d:\elearn.tmp\* d:\elearn.git\zenlin2 /s
echo �Х��T�{�ثe�� git tag ����
git add *
set /p var1=�п�J���p����������:
echo �ҿ�J���������� %var1%
git push -u origin main
set /p var2=�п�J�����n�Хܪ�git����:
echo �ҿ�J�����O��s��������%var2%
cd d:\elearn.git\zenlin2
git tag -am "%var1%"  %var2%
git push origin --tag


pause