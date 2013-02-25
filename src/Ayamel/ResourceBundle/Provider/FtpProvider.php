<?php

namespace Ayamel\ResourceBundle\Provider;

/**
 * Handler for ftp
 *
 * @author Evan Villemez
 */
class FtpProvider extends AbstractFilePathProvider
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'ftp';
    }

    /**
     * {@inheritdoc}
     */
    public function handlesScheme($scheme)
    {
        return in_array(strtolower($scheme), array('ftp','sftp'));
    }

}
