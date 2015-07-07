# 贡献指南

感谢您为本项目提交贡献，请使用 [Github](https://github.com/echo58/sms) 的 Pull Request 来为本项目贡献代码。

## Pull Requests

- **[遵循 PSR-2 代码标准](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - 使用 [PHP Coding Standards Fixer](http://cs.sensiolabs.org/) 可以帮您快速修正代码格式。

- **添加测试！** - 不接受任何没有配套单元测试的代码。

- **修改文档** - 请确保 `README.md` 和其它相关文档与代码行为相符。

- **发布周期** - 版本发布遵循 [SemVer v2.0.0](http://semver.org/)，请不要随意改变公共API。

- **创建功能分支（feature branch）** - 请不要从您的 master 分支发 Pull Request.

- **每次 Pull Request 仅包含一个功能** - 如果有多个功能贡献，请通过多个 Pull Request。

- **commit 历史有条理** - 请确保您 Pull Request 中每次 commit 有意义。如果您在开发过程中有很多过渡性的提交，请在 Pull Request 前 [合并它们](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages)。


## 运行单元测试

``` bash
$ composer test
```

## 修正代码格式

``` bash
$ php-cs-fixer fix
```
