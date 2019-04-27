/**
 * converts a number of seconds in the format HH:MM:SS
 */
function secondsFormat(seconds, format = 'h:m:s') {
    let h = Math.floor(seconds / 3600);
    let m = Math.floor((seconds % 3600) / 60);
    let s = seconds % 60;

    if (h < 10) h = '0' + h;
    if (m < 10) m = '0' + m;
    if (s < 10) s = '0' + s;

    let time = format.replace('h', h)
                     .replace('m', m)
                     .replace('s', s);

    return time;
}
