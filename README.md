PHP extensions required:

    PHP Zip


        /*
        $zip = new ZipArchive();

        $document_xml = 'word/document.xml';
        $template = '../documents/order_sheet.docx';
        $new_file = '../documents/temp/output.docx';

        copy($template, $new_file);
        
        if ($zip->open($new_file) === true) {
            $content = $zip->getFromName($document_xml);
            $content = str_replace('Name', 'Foo', $content);
        
            $zip->addFromString($document_xml, $content);
            $return = $zip->close();

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="foo.docx');

            readfile($new_file);
            unlink($new_file);
            exit;
        }
        */
