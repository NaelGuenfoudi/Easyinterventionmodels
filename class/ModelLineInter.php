<?php
/**
 * Class ModelLineInter
 *
 * Represents a model line of intervention.
 *
 * This file is part of Dolibarr ERP/CRM software.
 *
 * Copyrigth (C) 2023 Nael Guenfoudi <guenfmen@gmail.com>
 * @license GNU General Public License v3.0
 * @link https://www.dolibarr.org/
 */

class ModelLineInter
{

	protected $db;
	const table_name = 'modellineinter';
	public $id;
	public $name;
	public $content;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Insert a model instance in DB
	 * @return int -1 if error , else if model's inter
	 */
	public function create()
	{

		// Check if object already exists
		$this->name = filter_var($this->name, FILTER_SANITIZE_STRING);

		$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . self::table_name . " WHERE name ='" . $this->name . "'";


		$resql = $this->db->query($sql);
		if ($resql) {
			$row = $this->db->fetch_object($resql);
			if ($row) {
				// Object already exists, return -1 to indicate failure
				return -1;
			}
		}

		// Object does not exist, insert into database
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . self::table_name . " (name, content) VALUES ('" . $this->name . "', '" . $this->content . "')"; //');SHOW TABLES #
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . self::table_name, 'rowid');
			return $this->id;
		} else {
			return -1;
		}
	}

	/**
	 * Load a record from the database by its id
	 *
	 * @param int $id Id of the record to load
	 * @return bool True if the record was loaded successfully, false otherwise
	 */
	public function fetch($id)
	{
		$id = filter_id($id);
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . self::table_name . " WHERE id=$id";
		$resql = $this->db->query($sql, ['id' => $id]);
		if ($resql) {
			$record = $this->db->fetch_object($resql);
			if ($record) {
				$this->id = $record->id;
				$this->name = $record->name;
				$this->content = $record->content;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Update the record in the database
	 *
	 * @return bool True if the record was updated successfully, false otherwise
	 */
	public function update()
	{

		$sql = "UPDATE " . MAIN_DB_PREFIX . self::table_name . " SET name='" . $this->name . "', content='" . $this->content . "' WHERE rowid=" . (int)$this->id;
		print $sql;
		$resql = $this->db->query($sql);


		return $resql;
	}


	/**
	 * Delete the record from the database
	 *
	 * @return bool True if the record was deleted successfully, false otherwise
	 */
	public function delete()
	{
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . self::table_name . " WHERE rowid=" . $this->id;
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->id = null;
			$this->name = null;
			$this->content = null;
			return true;
		} else {
			return false;
		}
	}

	public function setModel($name, $content)
	{
		$this->name = $name;
		$this->content = $content;
	}


	/**
	 * Retrieves a list of objects from the database that match the specified criteria.
	 *
	 * @param DoliDb $db The database handler.
	 * @param array $criteria The search criteria.
	 *
	 * @return array An array of matching ModelLineInter objects.
	 */
	public static function getAll($db, $criteria = [])
	{
		$sql = "SELECT rowid,name,content FROM " . MAIN_DB_PREFIX . self::table_name;
		$params = [];

		if (!empty($criteria)) {
			foreach ($criteria as $key => $value) {
				if (!empty($value)) {
					$sql .= " AND $key = :$key";
					$params[$key] = $value;
				}
			}
		}

		$resql = $db->query($sql, $params);
		$objects = [];

		if ($resql) {
			while ($record = $db->fetch_object($resql)) {
				$model = new self($db);
				$model->id = $record->rowid;
				$model->name = $record->name;
				$model->content = $record->content;
				$objects[] = $model;
			}
		}

		return $objects;
	}

	/**
	 * Retrieves a list of objects from the database that match the specified criteria.
	 *
	 * @param DoliDb $db The database handler.
	 * @param array $criteria The search criteria.
	 *
	 * @return array An array of matching objects in the format ($id => $name).
	 */
	public static function getAllForm($db, $criteria = [])
	{
		$nameTable = MAIN_DB_PREFIX . self::table_name;
		$sql = "SELECT rowid, name FROM " . $nameTable;
		$params = [];

		if (!empty($criteria)) {
			foreach ($criteria as $key => $value) {
				if (!empty($value)) {
					$sql .= " AND $key = :$key";
					$params[$key] = $value;
				}
			}
		}
		$resql = $db->query($sql, $params);
		$objects = [];

		if ($resql) {
			while ($obj = $db->fetch_object($resql)) {
				$objects[$obj->rowid] = $obj->name;
			}
		}

		return $objects;
	}


	/**
	 * Retrieves an object from the database by its ID.
	 *
	 * @param int $id The ID of the object to retrieve.
	 * @param DoliDb $db The database handler.
	 *
	 * @return ModelLineInter|null The loaded object if it exists, null otherwise.
	 */
	public static function getById($id, $db)
	{
		$id = filter_var($id, FILTER_VALIDATE_INT);

		$sql = "SELECT rowid,name,content FROM " . MAIN_DB_PREFIX . self::table_name . " WHERE rowid = " . $id;
		$resql = $db->query($sql);

		if ($resql) {
			$record = $db->fetch_object($resql);
			if ($record) {
				$model = new ModelLineInter($db);
				$model->id = $record->rowid;
				$model->name = $record->name;
				$model->content = $record->content;
				return $model;
			}
		}

		return null;
	}

	/**
	 * Returns a HTML table with all the models of the intervention lines.
	 *
	 * @param  DoliDB $db  The Dolibarr database object
	 * @return string      The HTML table
	 */
	public static function getAllHtml($db)
	{
		// Get all models
		$models = self::getAll($db);

		// Initialize the HTML table with a header row
		$html = '<div><tr class="titre"><td class="nobordernopadding widthpictotitle valignmiddle col-picto"><span class="fas fa-list em080 infobox-contrat valignmiddle widthpictotitle pictotitle" style=""></span></td><td class="nobordernopadding valignmiddle col-title"><div class="titre inline-block">Modifier ligne</div></td></tr>';
		$html .= "<table class='noborder centpercent'><thead><tr class='liste_titre'><th colspan='2'>Modèles des lignes d'intervention</th></tr></thead><tbody>";

		// Loop through each model and add a row to the HTML table
		foreach ($models as $model) {
			if (GETPOST("action") == "editmodel" && GETPOST("id") == $model->id) {
				$content = empty($model->content) ? "VIDE" : $model->content;

				// Adding DolEditor for the input area of name and content.
				require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
				$doleditorName = new DolEditor('name-modify', $model->name, '', 200, 'dolibarr_notes', 'In', false, false, false, ROWS_5, '90%');
				$doleditorContent = new DolEditor('content-modify', $model->content, '', 200, 'dolibarr_notes', 'In', false, false, false, ROWS_5, '90%');

				// Add a hidden input field to send the 'id' value along with the POST request
				$html .= '<input type="hidden" name="id" value="' . $model->id . '">';

				// Add a row to the HTML table with a form for editing the model
				$html .= '<tr class="nowrap">
                        <td class="oddeven">' . $doleditorName->Create(1) . '</td>
                        <td class="oddeven">' . $doleditorContent->Create(1) . '</td> ' . self::getSaveCancelButton() . '
                      </tr>';
			} else {
				$content = empty($model->content) ? "VIDE" : $model->content;

				// Add a row to the HTML table with the model name, content, and edit/delete buttons
				$html .= '<tr class="nowrap">
                        <td class="oddeven">' . htmlspecialchars($model->name) . '</td>
                        <td class="oddeven">' . $content . '</td> ' . self::getEditDeleteButtons($model->id) . '
                      </tr>';
			}
		}

		// Close the HTML table
		$html .= '</tbody></table></div>';

		// Return the HTML table
		return $html;
	}

	/**
	 * Generates HTML buttons for editing and deleting a model.
	 * @param int $id The ID of the model.
	 * @return string The generated HTML buttons.
	 */
	private static function getEditDeleteButtons($id)
	{

		$out = '<td class="center">';
		$out .= '<a class="editfielda marginrightonly" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&action=editmodel&token=' . newToken() . '&model_id=' . $id . '#' . $id . '">';
		$out .= img_edit();
		$out .= '</a>';
		if ($id != 1) {
			$out .= '<a class="marginleftonly" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&action=ask_deletemodel&token=' . newToken() . '&model_id=' . $id . '">';
			$out .= img_delete();
			$out .= '</a>';
		}
		$out .= '</td>';
		return $out;
	}

	/**
	 * Generates HTML code for "Save" and "Cancel" buttons in a form.
	 * @param string $saveButtonName The name of the "Save" button (default: "save").
	 * @param string $cancelButtonName The name of the "Cancel" button (default: "cancel").
	 * @return string The generated HTML code for the buttons.
	 */
	private
	static function getSaveCancelButton($saveButtonName = 'save', $cancelButtonName = 'cancel')
	{
		global $langs;
		$html = '<td class="center" colspan="5" valign="center">';
		$html .= '<input type="submit" class="button buttongen marginbottomonly button-save" name="' . $saveButtonName . '" value="' . $langs->trans("Save") . '">';
		$html .= '<input type="submit" class="button buttongen marginbottomonly button-cancel" name="' . $cancelButtonName . '" value="' . $langs->trans("Cancel") . '">';
		$html .= '</td>';
		return $html;
	}


}
