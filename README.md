# Online-dictation-system
软件工程 
教师在线默写系统项目组
一、Product Backlog
摘要：
随着课堂教学方式的多样，传统教学方式要进行更新换代，以紧跟时代步伐。本项目就是要开发一套教师的教学默写系统，以帮助教师在课堂上，通过移动终端，在线自动批改等功能来实现提高教学效率，提升教学效果的目的。

业务需求：
教师与学校对此类商品可能有一定的需求。我通过考察市场发现没有此类的产品，此项目较有创新性。教师与校方可能希望通过引进该软件，促进教学效率的提高，并减轻教师的工作量。随着科技的发展，移动终端如pad和手机等在课堂中的地位变得越来越重要，应用也越来越广泛。
通过对微信平台的应用，使得老师可以更好的利用数据进行教学情况的评定，学生也可以根据自己的知识掌握情况进行更有针对性的学习。
本项目主要针对英语老师在课堂上的默写工作，具体要求为教师可以要求系统给学生在线推送中文，学生将英语发送给老师，系统对学生的答案自动批改，并且反馈给老师和学生。学校也可以利用这套系统进行教学质量的把关。

用户需求：
学生：1、注册，以便让老师进行分辨。
      2、请求进行默写测验，并在线将答案发送回去。
      3、得到自己默写的批改结果，以及自己以往的历史成绩及其统计值。
      4、得到自己默写成绩在班级里的排名。
      5、根据自己的错误历史进行有针对性的练习。
老师：1、进行学生的默写成绩的反馈，得到各个题目的错误率。
      2、向题目的数据库中增加、减少或更改题目。
      3、系统自动对学生的默写进行批改。
学校：1、提供学校统一的题库方便教师进行统一教学。
      2、得到各个教师的教学情况的统计，以对老师的教学质量进行把关。

功能需求：
ID	Name	Imp	Est	How to Demo	Notes
1	用户注册	200	2	新用户通过提交自己的学号（或工号）来获取相关权限和功能	无
2	教师输入题库	500	5	建立数据库，提供让教师输入、修改或删除默写题目和标准答案的功能，能够保存这些题目以便学生以后进行学习	题库用于给学生进行练习，同时答案用于下面批改的功能
3	学校输入统一题库	100	3	在基于提供给教师的输入接口的基础上预留给学校输入统一默写题目的接口，预留学校将统一题目导入系统的功能	有条件可以实现该功能
4	学生进行练习	600	5	教师设定推送默写的题号或章节范围，学生输入进行测试的指令，系统自动乱序推送一套默写，并接受学生的答案	考虑用微信平台或移动app的形式进行实现
5	批改	700	4	将学生的答案与数据库中预留答案进行比对，将批改结果发送给学生，系统自动记录学生的成绩等信息，并进行统计	提供学生的成绩、排名、平均分等信息
6	教师反馈	400	5	教师要可以查看每次默写的每个学生的成绩与每道默写的正确率，以及平均分、最高最低分等信息	无
7	学校反馈	150	3	基于给教师的反馈的程序，实现学校可以对每位教师的班级的平均分，最高最低分等信息的查询的功能	有条件可以实现
8	错题练习	300	5	系统记录每位同学的错误记录与所有同学的高错误率的题目，当学生发出要求进行错题练习的命令时，推送这些题目让学生练习	　无


可行性分析：
此类项目有类似的项目，但据体的实现和功能有较大的差异，可以借鉴这类系统的思想进行实现。功能可以通过数据库和Python等编程的语言实现。因此，该项目有较大的可行性。
