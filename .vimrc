"set wildignore+=sandbox/**

if ! exists('g:CommandTWildIgnore')
        let g:CommandTWildIgnore=''
endif

let g:CommandTWildIgnore.=",sandbox/**"
