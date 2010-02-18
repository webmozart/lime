#!/bin/sh

###
# This file is part of the Lime framework.
#
# (c) Fabien Potencier <fabien.potencier@symfony-project.com>
#
# This source file is subject to the MIT license that is bundled
# with this source code in the file LICENSE.
###

pear run-tests -r `dirname $0`/../unit
if [ -e "./run-tests.log" ]; then
  exit 1
fi
