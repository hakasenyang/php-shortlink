<?php
class ShortLink {
    /**
     * Please enter the correct DB information.
     * @var string
     */
    const DB_IP = '127.0.0.1';
    const DB_ID = 'hakase';
    const DB_PW = 'hakase';
    const DB_NAME = 'hakase';

    /**
     * mysqli Connect data.
     * @var class
     */
    private $mysqli;

    /**
     * Connect DB
     */
    public function __construct() {
        $this->mysqli = @new mysqli(self::DB_IP, self::DB_ID, self::DB_PW, self::DB_NAME);
        if ($this->mysqli->connect_errno) die('DB Connect ERROR!!!');
    }
    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int    $length      How many characters do we want?
     * @param string $keyspace    A string of all possible characters
     *                            to select from
     * @return string
     */
    private function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
    /**
     * DB Select Function
     * @param string $text  Short URL data
     * @param string $url   URL data (optional)
     * @return array Output DB data
     */
    private function Select($text, $url = NULL) {
        if (!is_null($text)) $text = htmlspecialchars($text, ENT_QUOTES);
        if (!is_null($url)) $url = htmlspecialchars($url, ENT_QUOTES);
        return $this->mysqli->query('select randstr, url from shortlink where url = \'' . $url . '\' or randstr = \'' . $text . '\';')->fetch_array(MYSQLI_ASSOC);
    }
    /**
     * Create a short URL address.
     * @param  string $url  URL data
     * @param  string $text Short URL data (optional)
     * @return array/null   When a valid value comes in,
     *                      it returns the source address and the short URL.
     *                      If it is not a valid value, it returns NULL.
     */
    public function Make($url, $text = NULL) {
        if (!is_NULL($text) &&
            (strlen($text) > 16 || strlen($text < 3))) return 1;
        $parse = parse_url($url);
        switch($parse['scheme'])
        {
            case 'http':
            case 'https':
                break;
            default:
                return 2;
        }
        if (is_null($parse['host'])) return 2;
        $select = $this->Select($text, $url);
        $url = htmlspecialchars($url, ENT_QUOTES);
        if ($text === NULL)
            $text = $this->random_str(rand(3, 16));
        if (!is_null($select['url']) &&
            $url === $select['url']) {
            return array('randstr' => $select['randstr'], 'url' => $select['url']);
        }
        if (!is_null($select['randstr']) &&
            $select['randstr'] === $text &&
            $select['url'] !== $url)
            return NULL;
        if (!is_null($select['randstr']))
            return $this->Make($url);
        $text = htmlspecialchars($text, ENT_QUOTES);
        return ($this->mysqli->query('INSERT INTO shortlink(randstr, url) VALUES (\'' . $text . '\', \'' . $url . '\');')) ? array('randstr' => $text, 'url' => $url) : 0;
    }
    /**
     * Returns the short URL as the source URL.
     * @param  string $text Short URL
     * @return string/null  Returns the source URL,
     *                      if there is a source URL, or NULL if there is no source URL.
     */
    public function LinkCheck($text) {
        $data = $this->Select($text);
        return (!is_null($data['url']) && !is_null($data['randstr'])) ? $data['url'] : NULL;
    }
    /**
     * Move the source URL to the page.
     * @param  string Short URL
     * @return null
     */
    public function move_header($text) {
        $data = $this->LinkCheck($text);
        if (!is_null($data)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
            header('Location: ' . htmlspecialchars_decode($data, ENT_QUOTES));
            die('<!doctype html><html><head><title>Move</title></head><body><a href="' . $data . '">Move</a></body></html>');
        }
        return NULL;
    }
}

$short = new ShortLink();

if ($_POST['url']) {
    $data = $short->Make($_POST['url'], $_POST['str']);
    if ($data === 1) die('Short');
    else if ($data === 2) die('This is not a valid URL address.');
    else if (!is_null($_POST['str']) &&
        (strlen($_POST['str']) > 16 || strlen($_POST['str']) < 3)) die('Short URL must be between 3 and 16 characters long.');
    else if ($data['randstr'] !== $_POST['str'] &&
        !is_null($_POST['str']) &&
        $data['url'] === $_POST['url']) die('Already registered - https://' . $_SERVER['HTTP_HOST'] . '/' . $data['randstr']);
    else if (!is_null($data['url'])) die('https://' . $_SERVER['HTTP_HOST'] . '/' . $data['randstr']);
    else die('Already registered short URL.');
}
if (strlen($_SERVER['QUERY_STRING']) >= 1)
    $short->move_header($_SERVER['QUERY_STRING']);
?>
<form method="post">
<input type="text" name="url" id="url" value="https://hakase.io/">
<input type="submit">
</form>
Hakase's Short Link Service
