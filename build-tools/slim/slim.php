<?php
/**
 * slim class.
 *
 * @package Pines
 * @subpackage com_slim
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Slim archiving class.
 *
 * Slim is an easy to implement, portable archiving format. It was originally
 * designed for PHP, but Slim can be easily implemented in most programming
 * languages.
 *
 * @package Pines
 * @subpackage com_slim
 */
class slim {
	/**
	 * Slim file format version.
	 */
	const slim_version = '1.0';
	/**
	 * The header data of the archive.
	 *
	 * The header array contains information about the archive:
	 *
	 * - files - An array of the files, directories, and links in the archive.
	 * - comp - Type of compression used on the archive.
	 * - compl - Level of compression used on the archive.
	 * - ichk - Whether integrity checks are used on the file data.
	 * - ext - Extra data.
	 *
	 * @var array
	 */
	private $header = array();
	/**
	 * The files to be written to the archive.
	 *
	 * @var array
	 */
	private $files = array();
	/**
	 * The entries (virtual files) to be written to the archive.
	 *
	 * @var array
	 */
	private $entries = array();
	/**
	 * The offset in bytes of the beginning of the file stream.
	 *
	 * @var int
	 */
	private $stream_offset;
	/**
	 * The compression filter's resource handle.
	 *
	 * @var resource
	 */
	private $compression_filter;
	/**
	 * The stub to place at the beginning of the file.
	 *
	 * The stub may begin with a shebang, such as:
	 *
	 * - #!/bin/sh
	 * - #! /usr/bin/php
	 *
	 * The next line (or the first line, if the shebang is omitted) *must* end
	 * with the string "slim1.0", such as:
	 *
	 * - slim1.0
	 * - <?php //slim1.0
	 * - #slim1.0
	 *
	 * The stub cannot contain a line with only the string "HEADER", because
	 * that line signifies the beginning of the archive header.
	 *
	 * @var string
	 */
	public $stub = 'slim1.0';
	/**
	 * Extra data to be included in the archive.
	 *
	 * This can be anything.
	 *
	 * @var array
	 */
	public $ext = array();
	/**
	 * The filename of the archive.
	 *
	 * @var string
	 */
	public $filename = '';
	/**
	 * Whether to compress the JSON header.
	 *
	 * Header compression always uses deflate. (RFC 1951)
	 *
	 * @var bool
	 */
	public $header_compression = true;
	/**
	 * The compression level (1-9) to use during header compression.
	 *
	 * -1 signifies default compression level.
	 *
	 * @var int
	 */
	public $header_compression_level = 9;
	/**
	 * The type of compression to use when saving the file.
	 *
	 * Currently the only compression types supported by this implementation of
	 * the Slim format are deflate and bzip2.
	 *
	 * @var string
	 */
	public $compression = 'deflate';
	/**
	 * The compression level (1-9) to use during compression.
	 *
	 * -1 signifies default compression level.
	 *
	 * Only works for deflate.
	 *
	 * @var int
	 */
	public $compression_level = 9;
	/**
	 * The directory to work within.
	 *
	 * When adding/extracting files, relative paths will be based on this path.
	 *
	 * @var string
	 */
	public $working_directory = '';
	/**
	 * Try to preserve file user and group.
	 *
	 * @var bool
	 */
	public $preserve_owner = false;
	/**
	 * Try to preserve file permissions.
	 *
	 * @var bool
	 */
	public $preserve_mode = false;
	/**
	 * Try to preserve file access/modified times.
	 *
	 * @var bool
	 */
	public $preserve_times = false;
	/**
	 * Use MD5 sums of files to check their integrity.
	 *
	 * @var bool
	 */
	public $file_integrity = false;
	/**
	 * Don't extract files into parent directories.
	 *
	 * This causes '..' directories to be changed to '__' (two underscores).
	 *
	 * @var bool
	 */
	public $no_parents = true;

	/**
	 * Add a slash to the end of a path, if it's not already there.
	 *
	 * @param string $path The path.
	 * @return string The new path.
	 */
	private function add_slash($path) {
		if ($path != '' && substr($path, -1) != '/')
			return "{$path}/";
		return $path;
	}

	/**
	 * Alter a path, with a regard to the current working directory.
	 *
	 * @param string $path The path to alter.
	 * @param bool $adding_working_dir Whether to add or strip the working directory.
	 * @return string The new path.
	 */
	private function make_path($path, $adding_working_dir = true) {
		if ($adding_working_dir) {
			if (substr($path, 1) != '/' && $this->working_directory != '') // && substr($path, strlen($this->working_directory)) != $this->working_directory)
				return $this->add_slash($this->working_directory) . $path;
			return $path;
		} else {
			if ($this->working_directory != '' && substr($path, 0, strlen($this->working_directory)) == $this->working_directory) // && substr($path, strlen($this->working_directory)) != $this->working_directory)
				return substr($path, strlen($this->working_directory));
			return $path;
		}
	}

	/**
	 * Apply the selected filters to a file handle.
	 *
	 * @param resource $handle The handle.
	 * @param string $mode 'r' for read filters, 'w' for write filters.
	 */
	private function apply_filters($handle, $mode) {
		switch ($this->compression) {
			case 'deflate':
				$this->compression_filter = stream_filter_append($handle, $mode == 'w' ? 'zlib.deflate' : 'zlib.inflate', $mode == 'w' ? STREAM_FILTER_WRITE : STREAM_FILTER_READ, $this->compression_level);
				break;
			case 'bzip2':
				$this->compression_filter = stream_filter_append($handle, $mode == 'w' ? 'bzip2.compress' : 'bzip2.decompress', $mode == 'w' ? STREAM_FILTER_WRITE : STREAM_FILTER_READ);
				break;
		}
	}

	/**
	 * Check a path according to a regex filter.
	 *
	 * @param string $path The path to check.
	 * @param string|array $filter A regex pattern or an array of regex patterns.
	 * @return bool True if the path does not match any of the filters, false otherwise.
	 */
	private function path_filter($path, $filter) {
		if (is_string($filter))
			return !preg_match($filter, $path);
		if (!is_array($filter))
			return false;
		foreach ($filter as $cur_filter) {
			if (preg_match($cur_filter, $path))
				return false;
		}
		return true;
	}

	/**
	 * Seek in a file.
	 *
	 * This function uses a workaround for seeking in compressed streams. It
	 * will use fread() instead of fseek().
	 *
	 * @param resource $handle The handle.
	 * @param int $offset The offset to seek to.
	 * @param int $whence When set to SEEK_CUR, $offset will be based on $this->stream_offset.
	 * @return int 0 on success, -1 on failure.
	 */
	private function fseek($handle, $offset, $whence = null) {
		// SEEK_CUR always seeks from $this->stream_offset.
		switch ($this->compression) {
			case 'deflate':
			case 'bzip2':
				if (isset($whence)) {
					if ($whence == SEEK_CUR){
						$distance = ftell($handle) - $this->stream_offset;
						if ($distance) {
							$test = $offset - $distance;
							if ($test < 0) {
								fseek($handle, 0);
								stream_filter_remove($this->compression_filter);
								fseek($handle, $this->stream_offset);
								$this->apply_filters($handle, 'r');
							} else {
								$offset = $test;
							}
						}
						if (!$offset)
							return 0;
						do {
							fread($handle, ($offset > 8192) ? 8192 : $offset);
							$offset -= 8192;
						} while ($offset > 0);
						return 0;
					}
					return fseek($handle, $offset, $whence);
				} else {
					return fseek($handle, $offset);
				}
				break;
			default:
				if ($whence == SEEK_CUR) {
					return fseek($handle, $this->stream_offset + $offset);
				} elseif (isset($whence)) {
					return fseek($handle, $offset, $whence);
				} else {
					return fseek($handle, $offset);
				}
				break;
		}
	}

	/**
	 * Add a directory to the archive.
	 *
	 * @param string $path The path of the directory.
	 * @param bool $contents Whether to add the contents of the directory.
	 * @param bool $recursive Whether to recurse into subdirectories.
	 * @param mixed $filter A regex pattern or an array of regex patterns, which when matches a path, it will be excluded.
	 * @param bool $exclude_vcs Whether to exclude SVN and CVS directories.
	 * @return bool True on success, false on failure. (All files being filtered is not considered a failure.)
	 */
	public function add_directory($path, $contents = true, $recursive = true, $filter = null, $exclude_vcs = true) {
		$rel_path = $this->add_slash($path);
		$abs_path = $this->add_slash($this->make_path($path));
		if ($abs_path != '' && !is_dir($abs_path))
			return false;
		if ($abs_path != '' && (is_null($filter) || $this->path_filter($rel_path, $filter)))
			$this->files[] = $abs_path;
		if (!$contents)
			return true;
		$dir_contents = scandir($abs_path == '' ? '.' : $abs_path);
		if ($dir_contents === false)
			return false;
		foreach ($dir_contents as $cur_path) {
			if ($cur_path == '.' || $cur_path == '..' || ($exclude_vcs && ($cur_path == '.hg' || $cur_path == '.hgtags' || $cur_path == '.svn' || $cur_path == '.cvs')) || (isset($filter) && !$this->path_filter($rel_path.$cur_path, $filter)))
				continue;
			if (is_file($abs_path.$cur_path)) {
				$this->files[] = $abs_path.$cur_path;
			} elseif (is_dir($abs_path.$cur_path)) {
				if ($recursive) {
					if (!$this->add_directory($rel_path.$cur_path, $contents, $recursive, $filter, $exclude_vcs))
						return false;
				} else {
					$this->files[] = $this->add_slash($abs_path.$cur_path);
				}
			}
		}
		return true;
	}

	/**
	 * Add a file to the archive.
	 *
	 * @param string $path The path of the file.
	 * @param mixed $filter A regex pattern or an array of regex patterns, which when matches a path, it will be excluded.
	 * @return bool True on success, false on failure. (The file being filtered is not considered a failure.)
	 */
	public function add_file($path, $filter = null) {
		if (!is_file($this->make_path($path)))
			return false;
		if (isset($filter) && !$this->path_filter($path, $filter))
			return true;
		$this->files[] = $this->make_path($path);
		return true;
	}
	
	/**
	 * Add an entry directly to the archive.
	 *
	 * You can add files and directories that don't actually exist using this
	 * function. The entry path must be relative to the root of the archive.
	 * (Absolute paths will be cleaned.)
	 *
	 * The entry array requires at least the following entries:
	 *
	 * - "type" - The type of entry. One of "link", "file", or "dir".
	 * - "path" - The path of the entry.
	 *
	 * If "type" is "link", the following entry is required:
	 *
	 * - "target" - The target of the symlink.
	 *
	 * If "type" is "file", the following entry is required:
	 *
	 * - "data" - The contents of the file.
	 *
	 * The following entries are optional:
	 *
	 * - "uid" - The user id of the entry. (preserve_owner)
	 * - "gid" - The group id of the entry. (preserve_owner)
	 * - "mode" - The protection mode of the entry. (preserve_mode)
	 * - "atime" - The last access time of the entry. (preserve_times)
	 * - "mtime" - The last modified time of the entry. (preserve_times)
	 *
	 * Be sure to add any parent directories first, before adding the entries
	 * they contain.
	 *
	 * @param array $entry The entry array.
	 * @return bool True on success, false on failure.
	 */
	public function add_entry($entry) {
		if (empty($entry) || !isset($entry['type']) || !isset($entry['path']) || $entry['path'] == '')
			return false;
		$new_entry = array(
			'type' => $entry['type'],
			'path' => preg_replace('/^\/+/', '', $entry['path'])
		);
		switch ($entry['type']) {
			case 'link':
				if (!isset($entry['target']) || $entry['target'] == '')
					return false;
				$new_entry['target'] = $entry['target'];
				break;
			case 'file':
				if (!isset($entry['data']))
					return false;
				$new_entry['data'] = (string) $entry['data'];
				break;
			case 'dir':
				$new_entry['path'] = $this->add_slash($new_entry['path']);
				break;
			default:
				return false;
				break;
		}
		if (isset($entry['uid']))
			$new_entry['uid'] = (int) $entry['uid'];
		if (isset($entry['gid']))
			$new_entry['gid'] = (int) $entry['gid'];
		if (isset($entry['mode']))
			$new_entry['mode'] = (int) $entry['mode'];
		if (isset($entry['atime']))
			$new_entry['atime'] = (int) $entry['atime'];
		if (isset($entry['mtime']))
			$new_entry['mtime'] = (int) $entry['mtime'];
		$this->entries[] = $new_entry;
		return true;
	}

	/**
	 * Write the archive to a file.
	 *
	 * @param string|null $filename The filename to write the archive to.
	 * @return bool True on success, false on failure.
	 */
	public function write($filename = NULL) {
		if (is_null($filename)) {
			$filename = $this->filename;
		} else {
			$this->filename = $filename;
		}
		unset($this->header['comp']);
		unset($this->header['compl']);
		if (!empty($this->compression)) {
			$this->header['comp'] = (string) $this->compression;
			if ($this->compression == 'deflate')
				$this->header['compl'] = (int) $this->compression_level;
		}
		$this->header['files'] = array();
		$this->header['ichk'] = (bool) $this->file_integrity;
		$this->header['ext'] = (array) $this->ext;
		$offset = 0.00;
		// Handle real files.
		foreach ($this->files as $cur_file) {
			$cur_path = $this->make_path($cur_file, false);
			if (is_link($cur_file)) {
				$new_array = array(
					'type' => 'link',
					'path' => $cur_path,
					'target' => readlink($cur_file)
				);
				$file_info = lstat($cur_file);
			} elseif (is_file($cur_file)) {
				$cur_file_size = (float) sprintf("%u", filesize($cur_file));
				$new_array = array(
					'type' => 'file',
					'path' => $cur_path,
					'offset' => $offset,
					'size' => $cur_file_size
				);
				if ($this->file_integrity)
					$new_array['md5'] = md5_file($cur_file);
				$offset += $cur_file_size;
				$file_info = stat($cur_file);
			} elseif (is_dir($cur_file)) {
				$new_array = array(
					'type' => 'dir',
					'path' => $cur_path
				);
				$file_info = stat($cur_file);
			} else {
				continue;
			}
			if ($this->preserve_owner) {
				$new_array['uid'] = $file_info['uid'];
				$new_array['gid'] = $file_info['gid'];
			}
			if ($this->preserve_mode)
				$new_array['mode'] = $file_info['mode'];
			if ($this->preserve_times) {
				$new_array['atime'] = $file_info['atime'];
				$new_array['mtime'] = $file_info['mtime'];
			}
			$this->header['files'][] = $new_array;
		}
		// Handle virtual files.
		foreach ($this->entries as $cur_entry) {
			$new_array = array(
				'type' => $cur_entry['type'],
				'path' => $cur_entry['path']
			);
			switch ($cur_entry['type']) {
				case 'link':
					$new_array['target'] = $cur_entry['target'];
					break;
				case 'file':
					$new_array['offset'] = $offset;
					$new_array['size'] = (float) sprintf("%u", strlen($cur_entry['data']));
					if ($this->file_integrity)
						$new_array['md5'] = md5($cur_entry['data']);
					$offset += $new_array['size'];
					break;
			}
			if ($this->preserve_owner) {
				if (isset($cur_entry['uid']))
					$new_array['uid'] = (int) $cur_entry['uid'];
				if (isset($cur_entry['gid']))
					$new_array['gid'] = (int) $cur_entry['gid'];
			}
			if ($this->preserve_mode && isset($cur_entry['mode']))
				$new_array['mode'] = (int) $cur_entry['mode'];
			if ($this->preserve_times) {
				if (isset($cur_entry['atime']))
					$new_array['atime'] = (int) $cur_entry['atime'];
				if (isset($cur_entry['mtime']))
					$new_array['mtime'] = (int) $cur_entry['mtime'];
			}
			$this->header['files'][] = $new_array;
		}
		if (!($fhandle = fopen($filename, 'w')))
			return false;
		$header = $this->header_compression ? 'D'.gzdeflate(json_encode($this->header), $this->header_compression_level) : json_encode($this->header);
		$before_stream = "{$this->stub}\nHEADER\n{$header}\nSTREAM\n";
		$this->stream_offset = strlen($before_stream);
		fwrite($fhandle, $before_stream);
		$this->apply_filters($fhandle, 'w');
		foreach ($this->files as $cur_file) {
			if (is_link($cur_file) || !is_file($cur_file))
				continue;
			if (!($fread = fopen($cur_file, 'r')))
				return false;
			@set_time_limit(21600);
			stream_copy_to_stream($fread, $fhandle);
		}
		foreach ($this->entries as $cur_entry) {
			if ($cur_entry['type'] != 'file')
				continue;
			fwrite($fhandle, $cur_entry['data']);
		}
		return fclose($fhandle);
	}

	/**
	 * Open an archive for reading.
	 *
	 * @param string $filename The filename of the archive to open.
	 * @return bool True on success, false on failure.
	 */
	public function read($filename = null) {
		if (is_null($filename)) {
			$filename = $this->filename;
		} else {
			$this->filename = $filename;
		}
		if (!file_exists($filename) || !($fhandle = fopen($filename, 'r')))
			return false;
		$this->stub = '';
		$check = fgets($fhandle);
		if (substr($check, 0, 2) == '#!') {
			$this->stub = $check;
			$check = fgets($fhandle);
		}
		if (substr($check, -8) != "slim1.0\n")
			return false;
		do {
			$this->stub .= $check;
			$check = fgets($fhandle);
		} while (!feof($fhandle) && $check != "HEADER\n");
		if (!($this->stub = substr($this->stub, 0, -1)))
			return false;
		$header = '';
		do {
			$header .= fgets($fhandle);
		} while (!feof($fhandle) && substr($header, -7) != "STREAM\n");
		if (substr($header, -7) != "STREAM\n" || !($header = substr($header, 0, -7)))
			return false;
		if (substr($header, 0, 1) == 'D')
			$header = gzinflate(substr($header, 1));
		if (!($this->header = json_decode($header, true)))
			return false;
		$this->compression = (string) $this->header['comp'];
		$this->compression_level = (int) $this->header['compl'];
		$this->file_integrity = (bool) $this->header['ichk'];
		$this->ext = (array) $this->header['ext'];
		$this->stream_offset = ftell($fhandle);
		return fclose($fhandle);
	}

	/**
	 * Get an array of information about files in the archive.
	 *
	 * @return array File information.
	 */
	public function get_current_files() {
		return $this->header['files'];
	}

	/**
	 * Return a file's content from the archive.
	 *
	 * @param string $filename The filename of the file to return.
	 * @return string The contents of the file.
	 */
	public function get_file($filename) {
		foreach ($this->header['files'] as $cur_entry) {
			if ($cur_entry['path'] != $filename || $cur_entry['type'] != 'file')
				continue;
			if (!($fhandle = fopen($this->filename, 'r')))
				return false;
			$this->fseek($fhandle, $this->stream_offset);
			$this->apply_filters($fhandle, 'r');
			$this->fseek($fhandle, $cur_entry['offset'], SEEK_CUR);
			do {
				$data = fread($fhandle, $cur_entry['size'] - strlen($data));
			} while (!feof($fhandle) && strlen($data) < $cur_entry['size']);
			fclose($fhandle);
			if ($this->file_integrity && $cur_entry['md5'] != md5($data))
				return false;
			return $data;
		}
		return false;
	}

	/**
	 * Extract from the archive.
	 *
	 * @param string $path The path of the file or directory to extract. If it is an empty string (''), the entire archive will be extracted.
	 * @param bool $recursive Whether to extract the contents of directories. (If false, only the directory will be created.)
	 * @param mixed $filter A regex pattern or an array of regex patterns, which when matches a path, it will be excluded.
	 * @return bool True on success, false on failure.
	 */
	public function extract($path = '', $recursive = true, $filter = null) {
		$return = true;
		$path_slash = $this->add_slash($path);
		if (!is_array($this->header['files']) || !($fhandle = fopen($this->filename, 'r')))
			return false;
		$this->fseek($fhandle, $this->stream_offset);
		$this->apply_filters($fhandle, 'r');
		foreach ($this->header['files'] as $cur_entry) {
			if ($path != '') {
				if ($recursive) {
					$cur_path_slash = $this->add_slash($cur_entry['path']);
					if ($cur_entry['path'] != $path && substr($cur_path_slash, 0, strlen($path_slash)) != $path_slash)
						continue;
				} else {
					if ($cur_entry['path'] != $path)
						continue;
				}
			}
			if (isset($filter) && !$this->path_filter($cur_entry['path'], $filter))
				continue;
			$cur_path = $this->make_path($cur_entry['path']);
			if ($this->no_parents)
				$cur_path = preg_replace('/(^|\/)\.\.(\/|$)/S', '__', $cur_path);
			switch ($cur_entry['type']) {
				case 'file':
					$this->fseek($fhandle, $cur_entry['offset'], SEEK_CUR);
					if (!($fwrite = fopen($cur_path, 'w'))) {
						$return = false;
						continue;
					}
					@set_time_limit(21600);
					$bytes = stream_copy_to_stream($fhandle, $fwrite, $cur_entry['size']);
					$return = $return && ($bytes == $cur_entry['size']) && fclose($fwrite);
					if ($this->file_integrity && $cur_entry['md5'] != md5_file($cur_path))
						$return = false;
					break;
				case 'dir':
					if (!is_dir($cur_path))
						$return = $return && mkdir($cur_path);
					break;
				case 'link':
					// TODO: Symlink owner/perms.
					// Save cwd.
					$cwd = getcwd();
					// Change to the current path's dir.
					if (!chdir(dirname($cur_path)))
						$return = false;
					// Make a symlink from that path.
					if (!is_file($cur_path))
						$return = $return && symlink($cur_entry['target'], basename($cur_path));
					// Change back to the original dir.
					if (!chdir($cwd))
						$return = false;
					break;
			}
			if ($this->preserve_owner && isset($cur_entry['uid']))
				chown($cur_path, $cur_entry['uid']);
			if ($this->preserve_owner && isset($cur_entry['gid']))
				chgrp($cur_path, $cur_entry['gid']);
			if ($this->preserve_mode && isset($cur_entry['mode']))
				chmod($cur_path, $cur_entry['mode']);
			if ($this->preserve_times && (isset($cur_entry['atime']) || isset($cur_entry['mtime'])))
				touch($cur_path, $cur_entry['mtime'], $cur_entry['atime']);
		}
		$return = $return && fclose($fhandle);
		return $return;
	}
}