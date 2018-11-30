// Copyright 2018 Building Energy Monitor.  All rights reserved.

function formatValue( rawValue )
{
  // Decide how to display the value
  var value = null;
  if ( rawValue === '' )
  {
    value = '';
  }
  else if ( ( rawValue === null ) || isNaN( rawValue ) )
  {
    value = String( rawValue );
  }
  else if ( ( -1 < rawValue ) && ( rawValue < 1 ) )
  {
    value = Math.round( rawValue * 100 ) / 100;
  }
  else
  {
    value = Math.round( rawValue );
  }

  return value;
}
