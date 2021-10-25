source ~/.vim/plugin/php-doc.vim
inoremap <C-P> <ESC>:call PhpDoc()<CR>i
nnoremap <C-P> :call PhpDoc()<CR>
vnoremap <C-P> :call PhpDoc()<CR>

augroup php
  " Remove all php autocommands
  au!

  au BufRead,BufNewFile *.php,*.inc,*.phtml set filetype=php
  au BufRead,BufNewFile *.tpl,*.html.*      set filetype=html
  au BufRead,BufNewFile *.xml,*.xml.tpl     set filetype=xml noautoindent
  au BufRead,BufNewFile *.sql               set filetype=sql noautoindent
  au BufRead,BufNewFile *.php,*.inc,*.phtml set cindent comments=sr:/*,mb:*,el:*/,://,:#
  au BufRead,BufNewFile *.php,*.inc highlight col80 ctermfg=white guibg=#666666
  au BufRead,BufNewFile *.php,*.inc match col80 /.\%>81v/
  au BufRead,BufNewFile *.php,*.inc highlight rightMargin ctermfg=white ctermbg=red guifg=#AA0000
  au BufRead,BufNewFile *.php,*.inc 2match rightMargin /.\%>120v/
  au BufWritePre *.php,*.inc,*.html,*.html.* :retab
  au BufNewFile,BufRead *.inc,*.php,*.phtml set efm=%E,%C%m\ in\ %f\ on\ line\ %l,%CErrors\ parsing\ %f,%C,%Z
  au BufNewFile,BufRead *.inc,*.php,*.phtml set makeprg=php\ -dlog_errors=off\ -ddisplay_errors=on\ -l\ %
  au BufNewFile,BufRead *.xml               set makeprg=xmlstarlet\ val\ %
  au BufWritePost *.inc,*.php,*.phtml,*.xml :make
"  au BufWritePost *.xml :make
  au BufWritePre * :%s/\s\+$//e
augroup END
