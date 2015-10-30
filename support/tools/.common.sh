set -o errexit
set -o nounset

chkcmd()
{
        if command -v "$1" > /dev/null 2>&1; then
                return 0
        fi
        return 1
}

watch()
{
        if ! chkcmd 'fswatch'; then
                echo ' error: "fswatch" command not found.'
                exit 1
        fi

        fswatch --latency 0.1 --print0 "$@"
}

dsstore_filter()
{
        while read -d '' e; do
                local dsstore=$(echo "$e" | grep -o "\.DS_Store")
                ## We don't use the exit status, because an exit status different from 0 terminates the script.
                ## Checking the output should be better than setting set +o errexit and then set -o errexit.
                if test "$dsstore" != '.DS_Store'; then
                        echo "$e\0"
                fi
        done
}
