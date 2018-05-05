# Region

数据来源： [2016年统计用区划代码和城乡划分代码(截止2016年07月31日)](http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2016/index.html)

## 统计用区划代码

一个十二位数字。

* 第1～2位，为省级代码；
* 第3～4位，为地级代码；
* 第5～6位，为县级代码；
* 第7～9位，为乡级代码；
* 第10～12位，为村级代码。

对于通常的电子商务等地址需求，不需要精确到村（据传淘宝和菜鸟也就四级），因此本数据里没有实现村级精度。

为了体现级别，超过该当级别的代码长度，尾部的零会被省略。

## 爬虫

如果你不放心或者啥的，可以自己爬一遍。

需要 PHP 7 + composer 。需要建立可写的`log`目录。

如果支持 `PHP-PCNTL` 库，推荐使用 `crawler/multi_crawler.php` 来开31个子进程提高效率。不然只能等O(31)的时间效率了。

运行

```bash
nohup php crawler/multi_crawler.php > log/nohup.log 2>&1 &
``` 

然后跑完坐等log里面收集31个sql文件。

## SQL （MySQL Dialect）

本版本的SQL导入在 `release/second` 目录下。首先导入表结构 `table.sql` 然后导入数据 `region.sql`。

## About Patch

如果出现了上下两级地区间隔空，例如广东某些市下面没有县（即地级下面直接就是乡级），所以为其补充了县级（代码为上级代码补零）。

如果实际遇到了更新版本的地址需要，在爬虫无效的情况下。可以自己找区划变革数据，然后写INSERT之类的SQL。


