/**
 * converts a number of seconds in the format HH:MM:SS
 */
function toHHMMSS(seconds) {
    let h = 0,
        m = 0,
        s = 0;

    h = Math.floor(seconds / 3600);
    m = Math.floor((seconds % 3600) / 60);
    s = seconds % 60;

    if (h < 10) h = '0' + h;
    if (m < 10) m = '0' + m;
    if (s < 10) s = '0' + s;

    return `${h}:${m}:${s}`;
}
