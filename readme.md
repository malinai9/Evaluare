# Moodle Plugin: Evaluare

This Moodle plugin allows the generation of reports for courses that contain fewer than 3 activities or resources, filtered by academic year and faculty. Users can download the generated reports in CSV format for further analysis.

# Note:

This plugin is only a small part of a more complex plugin system. It doesn't include the "db" folder or the "lib.php" file, which are typically present in a full Moodle plugin. As such, additional components and functionality might be required to make the plugin fully operational in a complete Moodle environment.

## **Main Features**

- Select an **academic year** from the available course categories.
- Select a **faculty** (optional).
- Generate a CSV report that includes courses with fewer than 3 activities or resources.
- Ability to download the generated report.
- Display an informational message if no courses meet the criteria.

---

## **Requirements**

- Moodle 3.x or a more recent version.
- Admin permissions to access this functionality (`moodle/category:manage` capability).

---

## **Installation**

1. Copy the plugin files to the `local/evaluare` directory in your Moodle instance.
2. Ensure the corresponding language file is added to the `lang` directory.
3. Navigate to the Moodle administration page to trigger the automatic installation of the plugin.

---

## **Usage**

1. Access the plugin page at:  
   `<Moodle URL>/local/evaluare/raport_activitate.php`.
2. Select an **academic year** from the available list.
3. (Optional) Select a **faculty**.
4. Click the **Search** button.
5. If any courses are found that meet the criteria, you will receive a link to download the CSV report.

---

## **Generated Files**

- **CSV:** Each generated report is temporarily saved in the Moodle `temp` directory. The filename includes the academic year and, if applicable, the faculty name (e.g., `report_activity_2023-2024_FacultyX.csv`).

---

## **CSV Report Structure**

The CSV report contains the following columns:

1. **Faculty** – The name of the faculty to which the course belongs.
2. **Course** – The full name of the course.
3. **Number of activities** – The total number of activities/resources in the course.
4. **Teacher** – The name(s) of the teacher(s) managing the course.

---

## **Customization**

To customize the plugin logic:

- **Add fields to the report:** Modify the section where the CSV columns are defined (in the `fputcsv` function).
- **Add additional filtering:** Extend the SQL queries to include other criteria.

---

## **Main Files**

- `raport_activitate.php`: The main file that defines the logic and interface of the plugin.
- `yearfaculty.js`: A custom JavaScript file that enables/disables the faculty dropdown based on the selected academic year.

---

## **Common Errors**

1. **CSV file is not created:**
   - Ensure the `temp` directory has the correct permissions for writing.
   - Check the `$CFG->dataroot` configuration in your Moodle config file.
2. **Faculty dropdown is not working:**
   - Verify that the `yearfaculty.js` file is correctly loaded.

---

## **Contributors**

- **Malina Ivan** - Primary author and developer.
